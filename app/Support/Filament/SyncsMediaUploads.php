<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait SyncsMediaUploads
{
    /**
     * Synchronize a file upload field with a Spatie media collection.
     *
     * The upload field's raw form state is keyed by Filament's upload UUIDs (even for
     * single-file fields), so it is normalized to a list of stored paths first.
     */
    protected function syncMediaCollection(string $fieldName, string $collectionName, bool $isMultiple = false): void
    {
        $disk = Storage::disk('public');

        $paths = array_values(array_filter(
            Arr::wrap($this->data[$fieldName] ?? null),
            fn (mixed $path): bool => is_string($path) && $disk->exists($path),
        ));

        if ($paths === []) {
            return;
        }

        if ($isMultiple) {
            foreach ($paths as $path) {
                $this->record->addMedia($disk->path($path))->toMediaCollection($collectionName);
            }

            return;
        }

        $path = Arr::last($paths);

        if ($this->record->getFirstMedia($collectionName)?->getPathRelativeToRoot() === $path) {
            return;
        }

        $this->record->clearMediaCollection($collectionName);
        $this->record->addMedia($disk->path($path))->toMediaCollection($collectionName);
    }

    /**
     * Make a multi-file collection mirror its upload field: media the admin removed
     * from the field is deleted, untouched media is kept and new uploads are added.
     * The edit page must pre-fill the field with {@see mediaPaths()}.
     */
    protected function replaceMediaCollection(string $fieldName, string $collectionName): void
    {
        $disk = Storage::disk('public');

        $paths = array_values(array_filter(
            Arr::wrap($this->data[$fieldName] ?? null),
            fn (mixed $path): bool => is_string($path) && $disk->exists($path),
        ));

        $existing = $this->record->getMedia($collectionName);

        $existing
            ->reject(fn (Media $media): bool => in_array($media->getPathRelativeToRoot(), $paths, true))
            ->each(fn (Media $media) => $media->delete());

        $existingPaths = $existing->map(fn (Media $media): string => $media->getPathRelativeToRoot())->all();

        foreach (array_diff($paths, $existingPaths) as $path) {
            $this->record->addMedia($disk->path($path))->toMediaCollection($collectionName);
        }
    }

    /**
     * @return list<string>
     */
    protected function mediaPaths(string $collectionName): array
    {
        return $this->record->getMedia($collectionName)
            ->map(fn (Media $media): string => $media->getPathRelativeToRoot())
            ->values()
            ->all();
    }
}
