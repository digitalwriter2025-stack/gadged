<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\BannerPlacement;
use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BannerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_admin_can_add_a_banner_with_artwork(): void
    {
        Livewire::test(CreateBanner::class)
            ->fillForm([
                'placement' => BannerPlacement::Promo->value,
                'title' => 'Smartphone Mega Sale',
                'alt_text' => 'Up to 25% off phones',
                'url' => '/category/smartphone',
                'open_in_new_tab' => true,
                'desktop_image' => UploadedFile::fake()->image('desktop.jpg', 1600, 603),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $banner = Banner::query()->where('title', 'Smartphone Mega Sale')->sole();

        $this->assertSame(BannerPlacement::Promo, $banner->placement);
        $this->assertTrue($banner->open_in_new_tab);
        $this->assertNotNull($banner->getFirstMedia('desktop'));
    }

    public function test_artwork_note_follows_the_chosen_placement(): void
    {
        Livewire::test(CreateBanner::class)
            ->assertSee('Hero slider artwork: 1672 × 941 px.')
            ->fillForm(['placement' => BannerPlacement::HeroSideTop->value])
            ->assertSee('Hero side (top) artwork: 418 × 216 px.')
            ->assertSee('Not used here: this slot shows the desktop image on phones too.');
    }

    public function test_admin_can_move_a_banner_to_another_placement(): void
    {
        $banner = Banner::factory()->create(['placement' => BannerPlacement::HeroMain]);

        Livewire::test(EditBanner::class, ['record' => $banner->getRouteKey()])
            ->assertOk()
            ->fillForm(['placement' => BannerPlacement::Promo->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(BannerPlacement::Promo, $banner->refresh()->placement);
    }
}
