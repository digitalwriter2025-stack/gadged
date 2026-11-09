<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Filament\Resources\HomeSections\Pages\CreateHomeSection;
use App\Filament\Resources\HomeSections\Pages\EditHomeSection;
use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CatalogCache;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HomeSectionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_every_section_is_listed_on_one_page_in_homepage_order(): void
    {
        $sections = collect(range(1, 20))->map(fn (int $position): HomeSection => HomeSection::query()->create([
            'type' => HomeSectionType::ProductCollection,
            'title' => "Block {$position}",
            'settings' => ['source' => ProductCollectionSource::Latest->value],
            'sort_order' => $position,
        ]));

        Livewire::test(ListHomeSections::class)
            ->assertOk()
            ->assertCanSeeTableRecords($sections, inOrder: true)
            ->assertSee('top to bottom');
    }

    public function test_each_row_says_in_plain_words_what_the_block_shows(): void
    {
        $category = Category::factory()->create(['name' => 'Power Banks']);

        HomeSection::query()->create(['type' => HomeSectionType::PromoBanners, 'settings' => []]);
        HomeSection::query()->create([
            'type' => HomeSectionType::ProductCollection,
            'title' => 'Power Banks',
            'settings' => ['source' => ProductCollectionSource::Category->value, 'category_id' => $category->id, 'limit' => 10],
        ]);
        $showcase = HomeSection::query()->create([
            'type' => HomeSectionType::Showcase,
            'title' => 'Stay charged',
            'settings' => ['source' => ProductCollectionSource::Manual->value, 'theme' => 'midnight'],
        ]);
        $showcase->items()->create(['itemable_type' => (new Product)->getMorphClass(), 'itemable_id' => Product::factory()->create()->id]);

        Livewire::test(ListHomeSections::class)
            ->assertSee('Promotional banners')
            ->assertSee('Banner images – change them in Banners')
            ->assertSee('Products from Power Banks, up to 10')
            ->assertSee('1 hand-picked product, black panel');
    }

    public function test_admin_can_hide_a_section_from_the_list(): void
    {
        $section = HomeSection::query()->create([
            'type' => HomeSectionType::ProductCollection,
            'title' => 'Best Sellers',
            'settings' => ['source' => ProductCollectionSource::BestSellers->value],
        ]);

        Livewire::test(ListHomeSections::class)
            ->call('updateTableColumnState', 'is_active', (string) $section->getKey(), false);

        $this->assertFalse($section->refresh()->is_active);
    }

    public function test_admin_can_move_a_block_one_step_up_or_down(): void
    {
        $hero = HomeSection::query()->create(['type' => HomeSectionType::Hero, 'settings' => [], 'sort_order' => 1]);
        $flashSale = HomeSection::query()->create(['type' => HomeSectionType::FlashSale, 'settings' => [], 'sort_order' => 2]);
        $brands = HomeSection::query()->create(['type' => HomeSectionType::FeaturedBrands, 'settings' => [], 'sort_order' => 3]);

        Livewire::test(ListHomeSections::class)
            ->callAction(TestAction::make('moveUp')->table($brands))
            ->assertCanSeeTableRecords([$hero, $brands, $flashSale], inOrder: true)
            ->callAction(TestAction::make('moveDown')->table($hero))
            ->assertCanSeeTableRecords([$brands, $hero, $flashSale], inOrder: true);

        $this->assertSame([1, 2, 3], [$brands->refresh()->sort_order, $hero->refresh()->sort_order, $flashSale->refresh()->sort_order]);
    }

    public function test_moving_a_block_works_when_positions_have_gaps_or_repeats(): void
    {
        $first = HomeSection::query()->create(['type' => HomeSectionType::Hero, 'settings' => [], 'sort_order' => 5]);
        $second = HomeSection::query()->create(['type' => HomeSectionType::FlashSale, 'settings' => [], 'sort_order' => 5]);
        $third = HomeSection::query()->create(['type' => HomeSectionType::FeaturedBrands, 'settings' => [], 'sort_order' => 40]);

        Livewire::test(ListHomeSections::class)
            ->assertTableColumnStateSet('position', 3, $third)
            ->callAction(TestAction::make('moveUp')->table($second))
            ->assertCanSeeTableRecords([$second, $first, $third], inOrder: true);

        $this->assertSame([1, 2, 3], [$second->refresh()->sort_order, $first->refresh()->sort_order, $third->refresh()->sort_order]);
    }

    public function test_the_top_block_cannot_move_up_and_the_bottom_block_cannot_move_down(): void
    {
        $top = HomeSection::query()->create(['type' => HomeSectionType::Hero, 'settings' => [], 'sort_order' => 1]);
        $bottom = HomeSection::query()->create(['type' => HomeSectionType::FlashSale, 'settings' => [], 'sort_order' => 2]);

        Livewire::test(ListHomeSections::class)
            ->assertActionDisabled(TestAction::make('moveUp')->table($top))
            ->assertActionEnabled(TestAction::make('moveDown')->table($top))
            ->assertActionEnabled(TestAction::make('moveUp')->table($bottom))
            ->assertActionDisabled(TestAction::make('moveDown')->table($bottom));
    }

    public function test_dragging_blocks_into_a_new_order_refreshes_the_storefront(): void
    {
        $hero = HomeSection::query()->create(['type' => HomeSectionType::Hero, 'settings' => [], 'sort_order' => 1]);
        $flashSale = HomeSection::query()->create(['type' => HomeSectionType::FlashSale, 'settings' => [], 'sort_order' => 2]);
        $cacheVersion = app(CatalogCache::class)->version();

        Livewire::test(ListHomeSections::class)
            ->call('reorderTable', [(string) $flashSale->getKey(), (string) $hero->getKey()]);

        $this->assertSame([1, 2], [$flashSale->refresh()->sort_order, $hero->refresh()->sort_order]);
        $this->assertGreaterThan($cacheVersion, app(CatalogCache::class)->version());
    }

    public function test_a_hidden_block_is_labelled_as_hidden(): void
    {
        $section = HomeSection::query()->create(['type' => HomeSectionType::ServiceHighlights, 'settings' => []]);

        Livewire::test(ListHomeSections::class)
            ->assertDontSee('Hidden from homepage');

        $section->update(['is_active' => false]);

        Livewire::test(ListHomeSections::class)
            ->assertSee('Hidden from homepage');
    }

    public function test_banner_blocks_point_to_where_their_pictures_are_edited(): void
    {
        $section = HomeSection::query()->create(['type' => HomeSectionType::PromoBanners, 'settings' => []]);

        Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->assertOk()
            ->assertSee('Edit: Promotional banners')
            ->assertSee('The pictures in this block are edited in Banners & Sliders')
            ->assertDontSee('Small text under the heading')
            ->assertDontSee('Where do the products come from?');
    }

    public function test_a_showcase_offers_heading_products_and_look_settings(): void
    {
        $section = HomeSection::query()->create([
            'type' => HomeSectionType::Showcase,
            'title' => 'New and Noteworthy',
            'settings' => ['source' => ProductCollectionSource::Latest->value, 'theme' => 'aurora'],
        ]);

        Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->assertOk()
            ->assertSee('Small text under the heading')
            ->assertSee('Where do the products come from?')
            ->assertSee('Panel colour')
            ->assertDontSee('edited in Banners & Sliders');
    }

    public function test_a_new_block_is_placed_at_the_bottom_of_the_homepage(): void
    {
        HomeSection::query()->create(['type' => HomeSectionType::FlashSale, 'settings' => [], 'sort_order' => 7]);

        Livewire::test(CreateHomeSection::class)
            ->assertFormSet(['sort_order' => 8]);
    }

    public function test_admin_can_create_a_showcase_with_hand_picked_products_and_collage_images(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $powerBank = Product::factory()->create();
        $charger = Product::factory()->create();

        Livewire::test(CreateHomeSection::class)
            ->fillForm([
                'type' => HomeSectionType::Showcase->value,
                'title' => 'Stay charged on the go',
                'settings' => [
                    'source' => ProductCollectionSource::Manual->value,
                    'theme' => 'midnight',
                    'limit' => 8,
                ],
                'images_upload' => [
                    UploadedFile::fake()->image('power-bank.png', 400, 400),
                    UploadedFile::fake()->image('charger.png', 400, 400),
                ],
                'items' => [
                    ['itemable_id' => $powerBank->id],
                    ['itemable_id' => $charger->id],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $section = HomeSection::query()->where('type', HomeSectionType::Showcase)->sole();

        $this->assertSame('midnight', $section->setting('theme'));
        $this->assertCount(2, $section->getMedia('images'));
        $this->assertSame(
            [$powerBank->id, $charger->id],
            $section->items->map(fn (HomeSectionItem $item): int => $item->itemable_id)->all(),
        );
        $this->assertSame(
            [(new Product)->getMorphClass()],
            $section->items->pluck('itemable_type')->unique()->values()->all(),
        );
    }

    public function test_a_hand_picked_product_shows_its_photo_beside_its_name(): void
    {
        $product = Product::factory()->create(['name' => 'Anker PowerCore 20000']);
        $photo = $product->addMedia(UploadedFile::fake()->image('anker.png', 400, 400))->toMediaCollection('main');
        $section = $this->handPickedShowcase($product);

        Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->call('callSchemaComponentMethod', $this->pickerKey($section), 'getOptionLabel')
            ->assertReturned(fn (string $label): bool => str_contains($label, 'src="'.e($photo->getAvailableUrl(['thumb'])).'"')
                && str_contains($label, 'Anker PowerCore 20000'));
    }

    public function test_product_search_finds_published_products_by_name_or_sku_only(): void
    {
        $anker = Product::factory()->create(['name' => 'Anker PowerCore 20000', 'sku' => 'ANK-PC20']);
        $baseus = Product::factory()->create(['name' => 'Baseus GaN Charger', 'sku' => 'BSS-GAN65']);
        Product::factory()->draft()->create(['name' => 'Anker Prototype']);
        $section = $this->handPickedShowcase($baseus);

        $search = fn (string $term) => Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->call('callSchemaComponentMethod', $this->pickerKey($section), 'getSearchResultsForJs', ['search' => $term]);

        $foundIds = fn (array $results): array => array_map(fn (array $option): int => (int) $option['value'], $results);

        $search('anker')->assertReturned(fn (array $results): bool => $foundIds($results) === [$anker->id]);
        $search('gan65')->assertReturned(fn (array $results): bool => $foundIds($results) === [$baseus->id]);
        $search('img')->assertReturned(fn (array $results): bool => $results === []);
    }

    public function test_saving_a_showcase_without_touching_its_images_keeps_them(): void
    {
        $section = HomeSection::query()->create([
            'type' => HomeSectionType::Showcase,
            'title' => 'New and Noteworthy',
            'settings' => ['source' => ProductCollectionSource::Latest->value, 'theme' => 'aurora'],
        ]);
        $image = $section->addMedia(UploadedFile::fake()->image('phone.png', 400, 400))->toMediaCollection('images');

        Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->fillForm(['title' => 'Fresh Arrivals'])
            ->call('save')
            ->assertHasNoFormErrors();

        $section->refresh();

        $this->assertSame('Fresh Arrivals', $section->title);
        $this->assertSame([$image->id], $section->getMedia('images')->pluck('id')->all());
    }

    public function test_removing_a_collage_image_deletes_it_from_the_showcase(): void
    {
        $section = HomeSection::query()->create([
            'type' => HomeSectionType::Showcase,
            'title' => 'New and Noteworthy',
            'settings' => ['source' => ProductCollectionSource::Latest->value, 'theme' => 'aurora'],
        ]);
        $kept = $section->addMedia(UploadedFile::fake()->image('kept.png', 400, 400))->toMediaCollection('images');
        $removed = $section->addMedia(UploadedFile::fake()->image('removed.png', 400, 400))->toMediaCollection('images');

        Livewire::test(EditHomeSection::class, ['record' => $section->getRouteKey()])
            ->fillForm(['images_upload' => [$kept->getPathRelativeToRoot()]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$kept->id], $section->refresh()->getMedia('images')->pluck('id')->all());
        Storage::disk('public')->assertMissing($removed->getPathRelativeToRoot());
    }

    private function handPickedShowcase(Product $product): HomeSection
    {
        $section = HomeSection::query()->create([
            'type' => HomeSectionType::Showcase,
            'title' => 'Stay charged',
            'settings' => ['source' => ProductCollectionSource::Manual->value, 'theme' => 'midnight'],
        ]);
        $section->items()->create(['itemable_type' => $product->getMorphClass(), 'itemable_id' => $product->id]);

        return $section;
    }

    /**
     * Schema key of the product select on the section's first hand-picked row.
     */
    private function pickerKey(HomeSection $section): string
    {
        return "form.items.record-{$section->items()->value('id')}.itemable_id";
    }
}
