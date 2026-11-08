<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CategoryTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_path_and_depth_are_set_on_create(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->childOf($root)->create();
        $grandchild = Category::factory()->childOf($child)->create();

        $this->assertSame("/{$root->id}/", $root->path);
        $this->assertSame(0, $root->depth);
        $this->assertSame("/{$root->id}/{$child->id}/{$grandchild->id}/", $grandchild->fresh()->path);
        $this->assertSame(2, $grandchild->fresh()->depth);
        $this->assertSame([$root->id, $child->id], $grandchild->fresh()->ancestorIds());
    }

    public function test_moving_a_category_rewrites_its_whole_subtree(): void
    {
        $components = Category::factory()->create();
        $laptops = Category::factory()->create();
        $storage = Category::factory()->childOf($components)->create();
        $ssd = Category::factory()->childOf($storage)->create();

        $storage->update(['parent_id' => $laptops->id]);

        $this->assertSame("/{$laptops->id}/{$storage->id}/", $storage->fresh()->path);
        $this->assertSame("/{$laptops->id}/{$storage->id}/{$ssd->id}/", $ssd->fresh()->path);
        $this->assertSame(2, $ssd->fresh()->depth);

        $storage->update(['parent_id' => null]);

        $this->assertSame("/{$storage->id}/{$ssd->id}/", $ssd->fresh()->path);
        $this->assertSame(1, $ssd->fresh()->depth);
    }

    public function test_a_category_cannot_be_moved_under_its_descendant(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->childOf($root)->create();

        $this->expectException(InvalidArgumentException::class);

        $root->update(['parent_id' => $child->id]);
    }

    public function test_a_category_cannot_be_its_own_parent(): void
    {
        $root = Category::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $root->update(['parent_id' => $root->id]);
    }

    public function test_subtree_ids_and_product_lookup_cover_descendants_only(): void
    {
        $components = Category::factory()->create();
        $gpu = Category::factory()->childOf($components)->create();
        $other = Category::factory()->create();

        $gpuProduct = Product::factory()->create(['category_id' => $gpu->id]);
        Product::factory()->create(['category_id' => $other->id]);

        $this->assertEqualsCanonicalizing([$components->id, $gpu->id], $components->subtreeIds());
        $this->assertTrue($components->isAncestorOf($gpu->fresh()));
        $this->assertSame(
            [$gpuProduct->id],
            Product::query()->inCategories($components->subtreeIds())->pluck('id')->all(),
        );
    }

    public function test_primary_category_is_always_in_the_pivot(): void
    {
        $first = Category::factory()->create();
        $second = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $first->id]);

        $product->update(['category_id' => $second->id]);

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $product->categories()->pluck('categories.id')->all());
    }

    public function test_a_category_with_children_cannot_be_force_deleted(): void
    {
        $root = Category::factory()->create();
        Category::factory()->childOf($root)->create();

        $this->expectException(QueryException::class);

        $root->forceDelete();
    }
}
