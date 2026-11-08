<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CatalogCache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Keeps categories.path / depth consistent and flushes the catalog cache.
 */
class CategoryObserver
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function saving(Category $category): void
    {
        if (! $category->isDirty('parent_id') || $category->parent_id === null) {
            return;
        }

        if ($category->exists && $category->parent_id === $category->id) {
            throw new InvalidArgumentException('A category cannot be its own parent.');
        }

        $parent = Category::query()->select(['id', 'path', 'depth'])->findOrFail($category->parent_id);

        if ($category->exists && str_contains((string) $parent->path, "/{$category->id}/")) {
            throw new InvalidArgumentException('A category cannot be moved under one of its own descendants.');
        }
    }

    public function created(Category $category): void
    {
        [$path, $depth] = $this->pathFor($category);

        $category->forceFill(['path' => $path, 'depth' => $depth])->saveQuietly();
        $this->cache->flush();
    }

    public function updated(Category $category): void
    {
        if ($category->wasChanged('parent_id')) {
            $this->moveSubtree($category);
        }

        $this->cache->flush();
    }

    public function deleted(Category $category): void
    {
        $this->cache->flush();
    }

    public function restored(Category $category): void
    {
        $this->cache->flush();
    }

    /**
     * Rewrites the path of the category and all its descendants in one statement.
     */
    private function moveSubtree(Category $category): void
    {
        $oldPath = (string) $category->path;
        $oldDepth = (int) $category->depth;
        [$newPath, $newDepth] = $this->pathFor($category);

        DB::transaction(function () use ($oldPath, $newPath, $newDepth, $oldDepth): void {
            Category::withTrashed()
                ->where('path', 'like', $oldPath.'%')
                ->update([
                    'path' => DB::raw('CONCAT('.DB::getPdo()->quote($newPath).', SUBSTRING(path, '.(strlen($oldPath) + 1).'))'),
                    'depth' => DB::raw('depth + ('.($newDepth - $oldDepth).')'),
                ]);
        });

        $category->forceFill(['path' => $newPath, 'depth' => $newDepth])->syncOriginal();
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function pathFor(Category $category): array
    {
        if ($category->parent_id === null) {
            return ["/{$category->id}/", 0];
        }

        $parent = Category::withTrashed()->select(['id', 'path', 'depth'])->findOrFail($category->parent_id);

        return [$parent->path.$category->id.'/', $parent->depth + 1];
    }
}
