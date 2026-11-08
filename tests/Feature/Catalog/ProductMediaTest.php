<?php

namespace Tests\Feature\Catalog;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_image_is_stored_with_webp_conversions(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $media = $product->addMedia(UploadedFile::fake()->image('headset.jpg', 1600, 1600))->toMediaCollection('main');

        $this->assertSame('main', $media->collection_name);
        $this->assertTrue($media->hasGeneratedConversion('card'));
        $this->assertTrue($media->hasGeneratedConversion('thumb'));
        $this->assertStringEndsWith('.webp', $media->getPath('card'));
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot('card'));

        // Single-file collection: a new upload replaces the old one.
        $product->addMedia(UploadedFile::fake()->image('headset-2.png', 800, 800))->toMediaCollection('main');
        $this->assertCount(1, $product->fresh()->getMedia('main'));
    }

    public function test_conversion_urls_point_at_each_media_file(): void
    {
        Storage::fake('public');
        $first = Product::factory()->create()->addMedia(UploadedFile::fake()->image('phone.jpg', 900, 900))->toMediaCollection('main');
        $second = Product::factory()->create()->addMedia(UploadedFile::fake()->image('laptop.png', 900, 900))->toMediaCollection('main');

        $this->assertInstanceOf(Media::class, $first);

        foreach ([$first, $second] as $media) {
            $this->assertSame(Storage::disk('public')->url($media->getPathRelativeToRoot('card')), $media->getUrl('card'));
            $this->assertStringEndsWith("/{$media->id}/conversions/".pathinfo($media->file_name, PATHINFO_FILENAME).'-card.webp', $media->getAvailableUrl(['card']));
        }

        $this->assertSame(Storage::disk('public')->url($first->getPathRelativeToRoot()), $first->getUrl());
    }

    public function test_unknown_conversion_url_is_refused(): void
    {
        Storage::fake('public');
        $media = Product::factory()->create()->addMedia(UploadedFile::fake()->image('phone.jpg'))->toMediaCollection('main');

        $this->expectException(InvalidConversion::class);

        $media->getUrl('poster');
    }

    public function test_svg_uploads_are_refused(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->expectException(FileUnacceptableForCollection::class);

        $product->addMedia($svg)->toMediaCollection('gallery');
    }
}
