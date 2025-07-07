<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\Category;
use App\Domain\Service\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create(['category_id' => $this->category->id]);
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'category_id',
            'name',
            'slug',
            'description',
            'sku',
            'price',
            'stock_quantity',
            'min_stock',
            'unit',
            'active',
        ];

        $this->assertEquals($fillable, $this->product->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'category_id' => 'int',
            'price' => 'decimal:2',
            'stock_quantity' => 'int',
            'min_stock' => 'int',
            'active' => 'boolean',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->product->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_category()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->product->category());
        $this->assertInstanceOf(Category::class, $this->product->category);
        $this->assertEquals($this->category->id, $this->product->category_id);
    }
    #[Test]
    public function it_has_many_service_items()
    {
        $this->assertInstanceOf(HasMany::class, $this->product->serviceItems());

        $serviceItem = ServiceItem::factory()->create(['product_id' => $this->product->id]);

        $this->assertTrue($this->product->serviceItems->contains($serviceItem));
    }
    #[Test]
    public function active_scope_returns_only_active_products()
    {
        Product::factory()->create(['active' => true]);
        Product::factory()->create(['active' => false]);
        Product::factory()->create(['active' => true]);

        $activeProducts = Product::active()->get();

        $this->assertEquals(3, $activeProducts->count()); // 2 + setUp product
        $this->assertTrue($activeProducts->every(fn($product) => $product->active === true));
    }
    #[Test]
    public function by_category_scope_filters_by_category()
    {
        $anotherCategory = Category::factory()->create();

        Product::factory()->create(['category_id' => $this->category->id]);
        Product::factory()->create(['category_id' => $anotherCategory->id]);

        $products = Product::byCategory($this->category->id)->get();

        $this->assertEquals(2, $products->count()); // 1 + setUp product
        $this->assertTrue($products->every(fn($product) => $product->category_id === $this->category->id));
    }
    #[Test]
    public function search_scope_filters_by_name_or_code()
    {
        Product::factory()->create(['name' => 'Óleo Motor', 'code' => 'OM001']);
        Product::factory()->create(['name' => 'Filtro Ar', 'code' => 'FA001']);
        Product::factory()->create(['name' => 'Óleo Câmbio', 'code' => 'OC001']);

        $results = Product::search('Óleo')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($product) => str_contains($product->name, 'Óleo')));
    }
    #[Test]
    public function low_stock_scope_returns_products_below_minimum()
    {
        Product::factory()->create(['minimum_stock' => 10, 'current_stock' => 5]);
        Product::factory()->create(['minimum_stock' => 10, 'current_stock' => 15]);
        Product::factory()->create(['minimum_stock' => 5, 'current_stock' => 3]);

        $lowStockProducts = Product::lowStock()->get();

        $this->assertEquals(2, $lowStockProducts->count());
        $this->assertTrue($lowStockProducts->every(fn($product) => $product->current_stock < $product->minimum_stock));
    }
    #[Test]
    public function out_of_stock_scope_returns_products_with_zero_stock()
    {
        Product::factory()->create(['current_stock' => 0]);
        Product::factory()->create(['current_stock' => 5]);
        Product::factory()->create(['current_stock' => 0]);

        $outOfStockProducts = Product::outOfStock()->get();

        $this->assertEquals(2, $outOfStockProducts->count());
        $this->assertTrue($outOfStockProducts->every(fn($product) => $product->current_stock === 0));
    }
    #[Test]
    public function in_stock_scope_returns_products_with_stock()
    {
        Product::factory()->create(['current_stock' => 10]);
        Product::factory()->create(['current_stock' => 0]);
        Product::factory()->create(['current_stock' => 5]);

        $inStockProducts = Product::inStock()->get();

        $this->assertEquals(3, $inStockProducts->count()); // 2 + setUp product
        $this->assertTrue($inStockProducts->every(fn($product) => $product->current_stock > 0));
    }
    #[Test]
    public function profit_margin_attribute_calculates_correctly()
    {
        $this->product->update([
            'cost_price' => 50.00,
            'sale_price' => 80.00
        ]);

        $expectedProfitMargin = 30.00; // 80 - 50
        $this->assertEquals($expectedProfitMargin, (float) $this->product->profitMargin);
    }
    #[Test]
    public function profit_percentage_attribute_calculates_correctly()
    {
        $this->product->update([
            'cost_price' => 50.00,
            'sale_price' => 80.00
        ]);

        $expectedProfitPercentage = 60.00; // ((80 - 50) / 50) * 100
        $this->assertEquals($expectedProfitPercentage, (float) $this->product->profitPercentage);
    }
    #[Test]
    public function stock_status_attribute_returns_correct_status()
    {
        // Test out of stock
        $this->product->update(['current_stock' => 0]);
        $this->assertEquals('out_of_stock', $this->product->stockStatus);

        // Test low stock
        $this->product->update(['minimum_stock' => 10, 'current_stock' => 5]);
        $this->assertEquals('low_stock', $this->product->stockStatus);

        // Test in stock
        $this->product->update(['minimum_stock' => 10, 'current_stock' => 15]);
        $this->assertEquals('in_stock', $this->product->stockStatus);
    }
    #[Test]
    public function update_stock_method_updates_current_stock()
    {
        $initialStock = $this->product->current_stock;
        $quantityChange = 10;

        $this->product->updateStock($quantityChange);

        $this->assertEquals($initialStock + $quantityChange, $this->product->fresh()->current_stock);
    }
    #[Test]
    public function update_stock_method_prevents_negative_stock()
    {
        $this->product->update(['current_stock' => 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estoque não pode ficar negativo');

        $this->product->updateStock(-10);
    }
    #[Test]
    public function is_low_stock_method_checks_stock_level()
    {
        $this->product->update(['minimum_stock' => 10, 'current_stock' => 5]);
        $this->assertTrue($this->product->isLowStock());

        $this->product->update(['current_stock' => 15]);
        $this->assertFalse($this->product->isLowStock());
    }
    #[Test]
    public function factory_creates_product_with_valid_data()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->code);
        $this->assertNotNull($product->category_id);
        $this->assertGreaterThan(0, $product->cost_price);
        $this->assertGreaterThan(0, $product->sale_price);
        $this->assertTrue($product->active);
    }
    #[Test]
    public function it_uses_soft_deletes()
    {
        $productId = $this->product->id;

        $this->product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNotNull($this->product->fresh()->deleted_at);
    }
    #[Test]
    public function it_can_restore_soft_deleted_product()
    {
        $this->product->delete();
        $this->assertSoftDeleted('products', ['id' => $this->product->id]);

        $this->product->restore();

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'deleted_at' => null
        ]);
    }
    #[Test]
    public function it_validates_price_constraints()
    {
        $this->product->update([
            'cost_price' => 100.00,
            'sale_price' => 150.00
        ]);

        $this->assertGreaterThan($this->product->cost_price, $this->product->sale_price);
    }
    #[Test]
    public function it_handles_decimal_precision_correctly()
    {
        $costPrice = 25.75;
        $salePrice = 40.50;

        $this->product->update([
            'cost_price' => $costPrice,
            'sale_price' => $salePrice
        ]);

        $this->assertEquals($costPrice, (float) $this->product->cost_price);
        $this->assertEquals($salePrice, (float) $this->product->sale_price);
    }
    #[Test]
    public function by_brand_scope_filters_by_brand()
    {
        Product::factory()->create(['brand' => 'Castrol']);
        Product::factory()->create(['brand' => 'Shell']);
        Product::factory()->create(['brand' => 'Castrol']);

        $results = Product::byBrand('Castrol')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($product) => $product->brand === 'Castrol'));
    }
    #[Test]
    public function by_supplier_scope_filters_by_supplier()
    {
        Product::factory()->create(['supplier' => 'Fornecedor A']);
        Product::factory()->create(['supplier' => 'Fornecedor B']);
        Product::factory()->create(['supplier' => 'Fornecedor A']);

        $results = Product::bySupplier('Fornecedor A')->get();

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($product) => $product->supplier === 'Fornecedor A'));
    }
    public function test_sku_is_always_uppercase_when_set()
    {
        $category = Category::factory()->create();
        
        $product = new Product();
        $product->name = 'Test Product';
        $product->sku = 'test-sku-123';
        $product->category_id = $category->id;
        $product->price = 100.00;
        $product->stock_quantity = 10;
        $product->unit = 'UN';
        $product->active = true;
        $product->save();

        $this->assertEquals('TEST-SKU-123', $product->sku);
    }
    public function test_sku_is_uppercase_with_spaces_trimmed()
    {
        $category = Category::factory()->create();
        
        $product = new Product();
        $product->name = 'Test Product';
        $product->sku = '  test-sku-456  ';
        $product->category_id = $category->id;
        $product->price = 100.00;
        $product->stock_quantity = 10;
        $product->unit = 'UN';
        $product->active = true;
        $product->save();

        $this->assertEquals('TEST-SKU-456', $product->sku);
    }
    public function test_sku_remains_uppercase_when_updated()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'sku' => 'ORIGINAL-SKU'
        ]);

        $product->sku = 'new-sku-789';
        $product->save();

        $this->assertEquals('NEW-SKU-789', $product->sku);
    }
    public function test_factory_generates_uppercase_sku()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);

        $this->assertEquals(strtoupper($product->sku), $product->sku);
    }
    public function test_sku_mutator_works_with_mass_assignment()
    {
        $category = Category::factory()->create();
        
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'mass-assign-sku',
            'category_id' => $category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'unit' => 'UN',
            'active' => true
        ]);

        $this->assertEquals('MASS-ASSIGN-SKU', $product->sku);
    }
    public function test_sku_mutator_works_with_update()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'sku' => 'ORIGINAL-SKU'
        ]);

        $product->update([
            'sku' => 'updated-sku-123'
        ]);

        $this->assertEquals('UPDATED-SKU-123', $product->sku);
    }
}
