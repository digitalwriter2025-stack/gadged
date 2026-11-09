<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_category_without_an_image(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Smart Watches',
                'slug' => 'smart-watches',
                'sort_order' => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'smart-watches')->sole();

        $this->assertSame('Smart Watches', $category->name);
        $this->assertSame(3, $category->sort_order);
        $this->assertNull($category->getFirstMedia('image'));
    }

    public function test_admin_can_add_a_category_with_an_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Headphones',
                'slug' => 'headphones',
                'image_upload' => UploadedFile::fake()->image('headphones.png', 400, 400),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::query()->where('slug', 'headphones')->sole();

        $this->assertNotNull($category->getFirstMedia('image'));
    }

    public function test_saving_a_category_without_touching_its_image_keeps_the_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
        $category = Category::factory()->create();
        $image = $category->addMedia(UploadedFile::fake()->image('old.png', 400, 400))->toMediaCollection('image');

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Renamed Category'])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();

        $this->assertSame('Renamed Category', $category->name);
        $this->assertSame($image->id, $category->getFirstMedia('image')?->id);
        Storage::disk('public')->assertExists($image->getPathRelativeToRoot());
    }

    public function test_uploading_a_new_image_replaces_the_category_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
        $category = Category::factory()->create();
        $oldImage = $category->addMedia(UploadedFile::fake()->image('old.png', 400, 400))->toMediaCollection('image');

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['image_upload' => UploadedFile::fake()->image('new.png', 400, 400)])
            ->call('save')
            ->assertHasNoFormErrors();

        $newImage = $category->refresh()->getFirstMedia('image');

        $this->assertNotNull($newImage);
        $this->assertNotSame($oldImage->id, $newImage->id);
        $this->assertCount(1, $category->getMedia('image'));
        Storage::disk('public')->assertMissing($oldImage->getPathRelativeToRoot());
    }
}
