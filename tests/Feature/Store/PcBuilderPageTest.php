<?php

namespace Tests\Feature\Store;

use App\Enums\PcBuilderComponent;
use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\PcBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcBuilderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_builder_lists_every_component_slot(): void
    {
        $this->get(route('store.pc-builder.show'))
            ->assertOk()
            ->assertSee('PC Builder - Build Your Own Computer')
            ->assertSee('Core Components')
            ->assertSee('Peripherals &amp; Others', false)
            ->assertSee('Graphics Card')
            ->assertSee('Wifi Adapter / LAN Card')
            ->assertSee('0 Items')
            ->assertSee(route('store.pc-builder.choose', PcBuilderComponent::Cpu), false);
    }

    public function test_choose_page_lists_only_published_products_from_the_component_category(): void
    {
        $processors = Category::factory()->create(['slug' => 'processor']);
        $monitors = Category::factory()->create(['slug' => 'monitor']);

        Product::factory()->for($processors)->create(['name' => 'Ryzen 7 7700']);
        Product::factory()->for($processors)->draft()->create(['name' => 'Draft Processor']);
        Product::factory()->for($monitors)->create(['name' => 'Some Monitor']);

        $this->get(route('store.pc-builder.choose', PcBuilderComponent::Cpu))
            ->assertOk()
            ->assertSee('Choose CPU')
            ->assertSee('Ryzen 7 7700')
            ->assertDontSee('Draft Processor')
            ->assertDontSee('Some Monitor');
    }

    public function test_choose_page_shows_empty_state_when_store_has_no_matching_category(): void
    {
        $this->get(route('store.pc-builder.choose', PcBuilderComponent::AntiVirus))
            ->assertOk()
            ->assertSee('No Anti Virus products are available right now.');
    }

    public function test_adding_a_product_updates_the_build_total(): void
    {
        $processors = Category::factory()->create(['slug' => 'processor']);
        $cards = Category::factory()->create(['slug' => 'graphics-card']);
        $cpu = Product::factory()->for($processors)->priced('25000.00')->create(['name' => 'Ryzen 7 7700']);
        $gpu = Product::factory()->for($cards)->priced('60000.00')->create(['name' => 'RTX 5070']);

        $this->post(route('store.pc-builder.store', PcBuilderComponent::Cpu), ['product_id' => $cpu->id])
            ->assertRedirect(route('store.pc-builder.show'));
        $this->post(route('store.pc-builder.store', PcBuilderComponent::GraphicsCard), ['product_id' => $gpu->id]);

        $this->get(route('store.pc-builder.show'))
            ->assertOk()
            ->assertSee('Ryzen 7 7700')
            ->assertSee('RTX 5070')
            ->assertSee('৳85,000')
            ->assertSee('2 Items')
            ->assertSee('Still required: Motherboard, RAM, Storage');
    }

    public function test_product_from_another_category_is_rejected(): void
    {
        Category::factory()->create(['slug' => 'processor']);
        $monitor = Product::factory()->for(Category::factory()->create(['slug' => 'monitor']))->create();

        $this->from(route('store.pc-builder.choose', PcBuilderComponent::Cpu))
            ->post(route('store.pc-builder.store', PcBuilderComponent::Cpu), ['product_id' => $monitor->id])
            ->assertRedirect(route('store.pc-builder.choose', PcBuilderComponent::Cpu))
            ->assertSessionHasErrors('product_id')
            ->assertSessionMissing(PcBuilderService::SESSION_KEY);
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $processors = Category::factory()->create(['slug' => 'processor']);
        $cpu = Product::factory()->for($processors)->priced('25000.00', stock: 0)->create();

        $this->assertSame(StockStatus::OutOfStock, $cpu->refresh()->stock_status);

        $this->post(route('store.pc-builder.store', PcBuilderComponent::Cpu), ['product_id' => $cpu->id])
            ->assertSessionHasErrors('product_id');
    }

    public function test_component_can_be_removed_and_build_cleared(): void
    {
        $processors = Category::factory()->create(['slug' => 'processor']);
        $cpu = Product::factory()->for($processors)->create(['name' => 'Ryzen 7 7700']);

        $this->withSession([PcBuilderService::SESSION_KEY => ['cpu' => $cpu->id, 'ram' => 999]])
            ->delete(route('store.pc-builder.remove', PcBuilderComponent::Cpu))
            ->assertRedirect(route('store.pc-builder.show'))
            ->assertSessionHas(PcBuilderService::SESSION_KEY, ['ram' => 999]);

        $this->delete(route('store.pc-builder.clear'))
            ->assertSessionMissing(PcBuilderService::SESSION_KEY);
    }

    public function test_unknown_component_returns_not_found(): void
    {
        $this->get('/tool/pc-builder/choose/toaster')->assertNotFound();
    }
}
