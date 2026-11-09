<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrandResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_brand_with_a_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateBrand::class)
            ->fillForm([
                'name' => 'Anker',
                'slug' => 'anker',
                'logo_upload' => UploadedFile::fake()->image('anker.png', 400, 400),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $brand = Brand::query()->where('slug', 'anker')->sole();

        $this->assertNotNull($brand->getFirstMedia('logo'));
    }
}
