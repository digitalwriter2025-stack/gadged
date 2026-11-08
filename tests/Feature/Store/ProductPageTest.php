<?php

namespace Tests\Feature\Store;

use App\Enums\SpecificationInputType;
use App\Models\Category;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\Review;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_product_page_shows_overview_pricing_and_breadcrumbs(): void
    {
        $parent = Category::factory()->create(['name' => 'Component']);
        $category = Category::factory()->childOf($parent)->create(['name' => 'Graphics Card']);
        $product = Product::factory()
            ->priced('124000.00', '118000.00', 5)
            ->create([
                'name' => 'Test RTX 5070 Ti Graphics Card',
                'category_id' => $category->id,
                'model' => 'GV-TEST-16G',
                'warranty' => '3 Years',
            ]);

        $response = $this->get(route('store.products.show', $product->slug));

        $response->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Test RTX 5070 Ti Graphics Card')
            ->assertSeeInOrder(['Home', 'Component', 'Graphics Card', $product->brand->name, 'Test RTX 5070 Ti Graphics Card'])
            ->assertSee('In Stock')
            ->assertSee('GV-TEST-16G')
            ->assertSee('৳118,000')
            ->assertSee('৳124,000')
            ->assertSee('Save: ৳6,000')
            ->assertSee('৳3,278')
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"price":"118000.00"', false)
            ->assertSee('data-product-page', false)
            ->assertSeeInOrder(['Specification', 'Description', 'Reviews (0)']);
    }

    public function test_hidden_products_return_not_found(): void
    {
        $draft = Product::factory()->draft()->create();
        $scheduled = Product::factory()->create(['published_at' => now()->addDay()]);
        $deleted = Product::factory()->create();
        $deleted->delete();

        $this->get(route('store.products.show', $draft->slug))->assertNotFound();
        $this->get(route('store.products.show', $scheduled->slug))->assertNotFound();
        $this->get(route('store.products.show', $deleted->slug))->assertNotFound();
        $this->get(route('store.products.show', 'missing-product'))->assertNotFound();
    }

    public function test_specifications_are_grouped_in_admin_order_with_key_features(): void
    {
        $product = Product::factory()->create(['warranty' => '3 Years']);
        $graphics = SpecificationGroup::query()->create(['name' => 'Graphics', 'slug' => 'graphics', 'sort_order' => 2]);
        $memory = SpecificationGroup::query()->create(['name' => 'Memory', 'slug' => 'memory', 'sort_order' => 1]);

        $size = $this->specification($memory, 'Memory Size', SpecificationInputType::Number, 1, 'GB');
        $clock = $this->specification($memory, 'Memory Clock', SpecificationInputType::Text, 2);
        $cores = $this->specification($graphics, 'CUDA Cores', SpecificationInputType::Number, 1);
        $ports = $this->specification($graphics, 'Output', SpecificationInputType::MultiSelect, 2);
        $displayPort = $ports->options()->create(['value' => 'DisplayPort 2.1b x3', 'slug' => 'dp']);
        $hdmi = $ports->options()->create(['value' => 'HDMI 2.1b x1', 'slug' => 'hdmi']);

        $product->specificationValues()->createMany([
            ['specification_id' => $cores->id, 'value' => '8960'],
            ['specification_id' => $clock->id, 'value' => '28 Gbps'],
            ['specification_id' => $size->id, 'value' => '16', 'value_numeric' => '16'],
            ['specification_id' => $ports->id, 'specification_option_id' => $displayPort->id],
            ['specification_id' => $ports->id, 'specification_option_id' => $hdmi->id],
        ]);

        $response = $this->get(route('store.products.show', $product->slug));

        $response->assertOk()
            ->assertSeeInOrder([
                'Key Features', 'Memory Size: 16 GB', 'Memory Clock: 28 Gbps', 'CUDA Cores: 8960', 'Output: DisplayPort 2.1b x3, HDMI 2.1b x1',
                'Specifications', 'Memory', 'Memory Size', '16 GB', 'Memory Clock', '28 Gbps',
                'Graphics', 'CUDA Cores', '8960', 'Output', 'DisplayPort 2.1b x3', 'HDMI 2.1b x1',
                'Warranty Information', '3 Years',
            ]);
    }

    public function test_description_is_sanitized(): void
    {
        $product = Product::factory()->create([
            'description' => '<h2>Technical Details</h2><p>Fast card</p><script>alert("x")</script><img src="x" onerror="alert(1)">',
        ]);

        $response = $this->get(route('store.products.show', $product->slug));

        $response->assertOk()
            ->assertSee('<h2>Technical Details</h2>', false)
            ->assertSee('Fast card')
            ->assertDontSee('alert("x")', false)
            ->assertDontSee('onerror', false);
    }

    public function test_related_products_come_from_the_same_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->priced('60000.00', '55000.00')->create(['category_id' => $category->id, 'name' => 'Sibling Graphics Card']);
        Product::factory()->draft()->create(['category_id' => $category->id, 'name' => 'Draft Sibling Card']);
        Product::factory()->create(['name' => 'Unrelated Monitor']);

        $response = $this->get(route('store.products.show', $product->slug));

        $response->assertOk()
            ->assertSee('Related Products')
            ->assertSee('Sibling Graphics Card')
            ->assertSee('৳55,000')
            ->assertSee('Save: ৳5,000')
            ->assertDontSee('Draft Sibling Card')
            ->assertDontSee('Unrelated Monitor');
    }

    public function test_only_approved_reviews_are_listed(): void
    {
        $product = Product::factory()->create();
        Review::factory()->approved()->for($product)->create(['title' => 'Runs every game at 4K', 'rating' => 5]);
        Review::factory()->for($product)->create(['title' => 'Pending opinion']);
        $product->forceFill(['rating_avg' => 5, 'rating_count' => 1])->save();

        $response = $this->get(route('store.products.show', $product->slug));

        $response->assertOk()
            ->assertSee('Reviews (1)')
            ->assertSee('Runs every game at 4K')
            ->assertDontSee('Pending opinion')
            ->assertDontSee('No reviews yet');
    }

    public function test_empty_reviews_show_a_login_prompt(): void
    {
        $product = Product::factory()->create();

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('No reviews yet')
            ->assertSee('Login to write a review.');
    }

    public function test_running_flash_sale_price_is_shown(): void
    {
        $product = Product::factory()->priced('10000.00')->create();
        FlashSaleProduct::factory()->for($product)->create(['discount_value' => '20.00']);

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('৳8,000')
            ->assertSee('Save: ৳2,000');
    }

    public function test_emi_is_hidden_below_the_minimum_price(): void
    {
        $product = Product::factory()->priced('1000.00')->create();

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('EMI starts from');
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $product = Product::factory()->priced('5000.00', null, 0)->create();

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Out of Stock')
            ->assertSee('disabled', false)
            ->assertDontSee('Add to Cart');
    }

    public function test_viewing_the_page_increments_the_view_count(): void
    {
        $product = Product::factory()->create();

        $this->get(route('store.products.show', $product->slug))->assertOk();
        $this->get(route('store.products.show', $product->slug))->assertOk();

        $this->assertSame(2, $product->fresh()->view_count);
    }

    public function test_every_referenced_local_image_exists(): void
    {
        $product = Product::factory()->create();

        $html = $this->get(route('store.products.show', $product->slug))->assertOk()->getContent();

        preg_match_all('#/(images/[^"\s]+)#', $html, $matches);

        $this->assertContains('images/products/placeholder.svg', $matches[1]);

        foreach (array_unique($matches[1]) as $path) {
            $this->assertFileExists(public_path($path));
        }
    }

    private function specification(SpecificationGroup $group, string $name, SpecificationInputType $type, int $order, ?string $unit = null): Specification
    {
        return Specification::query()->create([
            'specification_group_id' => $group->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'input_type' => $type,
            'unit' => $unit,
            'sort_order' => $order,
        ]);
    }

    public function test_offer_faqs_and_category_links_are_shown(): void
    {
        $category = Category::factory()->create(['name' => 'Gaming PC']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'offer_text' => 'Free Gaming Mouse',
            'faqs' => [
                ['question' => 'What processor does this PC use?', 'answer' => 'AMD Ryzen 7 7700.'],
                ['question' => '', 'answer' => 'Ignored'],
            ],
        ]);

        $this->get(route('store.products.show', $product->slug))
            ->assertOk()
            ->assertSee('Free Gaming Mouse')
            ->assertSeeInOrder(['Specification', 'Description', 'FAQs', 'Reviews (0)'])
            ->assertSee('What processor does this PC use?')
            ->assertDontSee('Ignored')
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee(route('store.categories.show', $category->slug));
    }
}
