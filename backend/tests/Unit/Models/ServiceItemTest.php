<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Service\Models\ServiceItem;
use App\Domain\Service\Models\Service;
use App\Domain\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ServiceItem $serviceItem;
    protected Service $service;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Service::factory()->create();
        $this->product = Product::factory()->create();

        $this->serviceItem = ServiceItem::factory()->create([
            'service_id' => $this->service->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'discount_percentage' => 10
        ]);
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'service_id',
            'product_id',
            'quantity',
            'unit_price',
            'discount_percentage',
            'total_price',
            'description',
            'notes'
        ];

        $this->assertEquals($fillable, $this->serviceItem->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'service_id' => 'int',
            'product_id' => 'int',
            'quantity' => 'int',
            'unit_price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->serviceItem->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_service()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->serviceItem->service());
        $this->assertInstanceOf(Service::class, $this->serviceItem->service);
        $this->assertEquals($this->service->id, $this->serviceItem->service_id);
    }
    #[Test]
    public function it_belongs_to_product()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->serviceItem->product());
        $this->assertInstanceOf(Product::class, $this->serviceItem->product);
        $this->assertEquals($this->product->id, $this->serviceItem->product_id);
    }
    #[Test]
    public function subtotal_attribute_calculates_quantity_times_unit_price()
    {
        $expectedSubtotal = 2 * 50.00; // quantity * unit_price
        $this->assertEquals($expectedSubtotal, (float) $this->serviceItem->subtotal);
    }
    #[Test]
    public function discount_amount_attribute_calculates_discount_value()
    {
        $subtotal = 2 * 50.00; // 100.00
        $expectedDiscountAmount = $subtotal * (10 / 100); // 10.00
        $this->assertEquals($expectedDiscountAmount, (float) $this->serviceItem->discountAmount);
    }
    #[Test]
    public function final_price_attribute_applies_discount_to_subtotal()
    {
        $subtotal = 2 * 50.00; // 100.00
        $discountAmount = $subtotal * (10 / 100); // 10.00
        $expectedFinalPrice = $subtotal - $discountAmount; // 90.00

        $this->assertEquals($expectedFinalPrice, (float) $this->serviceItem->finalPrice);
    }
    #[Test]
    public function calculate_total_method_returns_final_price()
    {
        $expectedTotal = 90.00; // From previous calculations
        $calculatedTotal = $this->serviceItem->calculateTotal();

        $this->assertEquals($expectedTotal, $calculatedTotal);
    }
    #[Test]
    public function calculate_total_method_handles_zero_discount()
    {
        $this->serviceItem->update(['discount_percentage' => 0]);

        $expectedTotal = 2 * 50.00; // No discount
        $calculatedTotal = $this->serviceItem->calculateTotal();

        $this->assertEquals($expectedTotal, $calculatedTotal);
    }
    #[Test]
    public function calculate_total_method_handles_hundred_percent_discount()
    {
        $this->serviceItem->update(['discount_percentage' => 100]);

        $expectedTotal = 0.00; // 100% discount
        $calculatedTotal = $this->serviceItem->calculateTotal();

        $this->assertEquals($expectedTotal, $calculatedTotal);
    }
    #[Test]
    public function it_validates_quantity_is_positive()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantidade deve ser maior que zero');

        ServiceItem::factory()->create(['quantity' => 0]);
    }
    #[Test]
    public function it_validates_unit_price_is_positive()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Preço unitário deve ser maior que zero');

        ServiceItem::factory()->create(['unit_price' => -10.00]);
    }
    #[Test]
    public function it_validates_discount_percentage_range()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Percentual de desconto deve estar entre 0 e 100');

        ServiceItem::factory()->create(['discount_percentage' => 150]);
    }
    #[Test]
    public function is_profitable_method_checks_if_item_generates_profit()
    {
        // Set product cost price lower than service item unit price
        $this->product->update(['cost_price' => 30.00]);
        $this->serviceItem->update(['unit_price' => 50.00]);

        $this->assertTrue($this->serviceItem->isProfitable());

        // Set product cost price higher than service item unit price
        $this->product->update(['cost_price' => 60.00]);

        $this->assertFalse($this->serviceItem->isProfitable());
    }
    #[Test]
    public function profit_amount_attribute_calculates_profit_per_unit()
    {
        $this->product->update(['cost_price' => 30.00]);
        $this->serviceItem->update(['unit_price' => 50.00]);

        $expectedProfitAmount = 50.00 - 30.00; // unit_price - cost_price
        $this->assertEquals($expectedProfitAmount, (float) $this->serviceItem->profitAmount);
    }
    #[Test]
    public function profit_margin_attribute_calculates_total_profit()
    {
        $this->product->update(['cost_price' => 30.00]);
        $this->serviceItem->update([
            'quantity' => 2,
            'unit_price' => 50.00,
            'discount_percentage' => 10
        ]);

        $finalPrice = 90.00; // As calculated before
        $totalCost = 2 * 30.00; // quantity * cost_price
        $expectedProfitMargin = $finalPrice - $totalCost; // 90 - 60 = 30

        $this->assertEquals($expectedProfitMargin, (float) $this->serviceItem->profitMargin);
    }
    #[Test]
    public function factory_creates_service_item_with_valid_data()
    {
        $serviceItem = ServiceItem::factory()->create();

        $this->assertInstanceOf(ServiceItem::class, $serviceItem);
        $this->assertNotNull($serviceItem->service_id);
        $this->assertNotNull($serviceItem->product_id);
        $this->assertGreaterThan(0, $serviceItem->quantity);
        $this->assertGreaterThan(0, $serviceItem->unit_price);
        $this->assertGreaterThanOrEqual(0, $serviceItem->discount_percentage);
        $this->assertLessThanOrEqual(100, $serviceItem->discount_percentage);
    }
    #[Test]
    public function it_handles_decimal_precision_correctly()
    {
        $unitPrice = 25.75;
        $discountPercentage = 12.5;

        $this->serviceItem->update([
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage
        ]);

        $this->assertEquals($unitPrice, (float) $this->serviceItem->unit_price);
        $this->assertEquals($discountPercentage, (float) $this->serviceItem->discount_percentage);
    }
    #[Test]
    public function it_updates_total_price_when_values_change()
    {
        $this->serviceItem->update([
            'quantity' => 3,
            'unit_price' => 40.00,
            'discount_percentage' => 15
        ]);

        $expectedSubtotal = 3 * 40.00; // 120.00
        $expectedDiscount = $expectedSubtotal * (15 / 100); // 18.00
        $expectedFinalPrice = $expectedSubtotal - $expectedDiscount; // 102.00

        $this->assertEquals($expectedFinalPrice, (float) $this->serviceItem->finalPrice);
    }
    #[Test]
    public function it_calculates_correct_values_with_fractional_quantities()
    {
        $this->serviceItem->update([
            'quantity' => 2.5, // Assuming decimal quantities are allowed
            'unit_price' => 40.00,
            'discount_percentage' => 20
        ]);

        $expectedSubtotal = 2.5 * 40.00; // 100.00
        $expectedDiscount = $expectedSubtotal * (20 / 100); // 20.00
        $expectedFinalPrice = $expectedSubtotal - $expectedDiscount; // 80.00

        $this->assertEquals($expectedFinalPrice, (float) $this->serviceItem->finalPrice);
    }
    #[Test]
    public function by_service_scope_filters_by_service()
    {
        $anotherService = Service::factory()->create();

        ServiceItem::factory()->create(['service_id' => $this->service->id]);
        ServiceItem::factory()->create(['service_id' => $anotherService->id]);

        $items = ServiceItem::byService($this->service->id)->get();

        $this->assertEquals(2, $items->count()); // 1 + setUp serviceItem
        $this->assertTrue($items->every(fn($item) => $item->service_id === $this->service->id));
    }
    #[Test]
    public function by_product_scope_filters_by_product()
    {
        $anotherProduct = Product::factory()->create();

        ServiceItem::factory()->create(['product_id' => $this->product->id]);
        ServiceItem::factory()->create(['product_id' => $anotherProduct->id]);

        $items = ServiceItem::byProduct($this->product->id)->get();

        $this->assertEquals(2, $items->count()); // 1 + setUp serviceItem
        $this->assertTrue($items->every(fn($item) => $item->product_id === $this->product->id));
    }
    #[Test]
    public function it_has_proper_table_name()
    {
        $this->assertEquals('service_items', $this->serviceItem->getTable());
    }
    #[Test]
    public function it_has_proper_primary_key()
    {
        $this->assertEquals('id', $this->serviceItem->getKeyName());
        $this->assertTrue($this->serviceItem->getIncrementing());
    }
}
