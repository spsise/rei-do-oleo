<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\Category;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSkuUppercaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_sku_is_uppercase_when_creating_product_via_api()
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'test-sku-api-123',
            'description' => 'Test description',
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'min_stock' => 5,
            'unit' => 'UN',
            'active' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        
        $product = Product::where('name', 'Test Product')->first();
        $this->assertEquals('TEST-SKU-API-123', $product->sku);
    }

    public function test_sku_is_uppercase_when_updating_product_via_api()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'sku' => 'ORIGINAL-SKU'
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'sku' => 'updated-sku-api-456',
            'description' => 'Updated description',
            'category_id' => $this->category->id,
            'price' => 150.00,
            'stock_quantity' => 20,
            'min_stock' => 10,
            'unit' => 'UN',
            'active' => true
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        
        $product->refresh();
        $this->assertEquals('UPDATED-SKU-API-456', $product->sku);
    }

    public function test_sku_with_spaces_is_trimmed_and_uppercase()
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => '  test-sku-spaces  ',
            'description' => 'Test description',
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'min_stock' => 5,
            'unit' => 'UN',
            'active' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        
        $product = Product::where('name', 'Test Product')->first();
        $this->assertEquals('TEST-SKU-SPACES', $product->sku);
    }

    public function test_sku_with_mixed_case_is_converted_to_uppercase()
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'MiXeD-cAsE-sKu',
            'description' => 'Test description',
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 10,
            'min_stock' => 5,
            'unit' => 'UN',
            'active' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        
        $product = Product::where('name', 'Test Product')->first();
        $this->assertEquals('MIXED-CASE-SKU', $product->sku);
    }
} 