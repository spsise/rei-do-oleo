<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;
use App\Domain\Product\Models\Category;
use App\Domain\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'name',
            'slug',
            'description',
            'active',
            'sort_order',
        ];

        $this->assertEquals($fillable, $this->category->getFillable());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'parent_id' => 'int',
            'order' => 'int',
            'active' => 'boolean',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->category->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_parent_category()
    {
        $parentCategory = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertInstanceOf(BelongsTo::class, $childCategory->parent());
        $this->assertInstanceOf(Category::class, $childCategory->parent);
        $this->assertEquals($parentCategory->id, $childCategory->parent_id);
    }
    #[Test]
    public function it_has_many_children_categories()
    {
        $this->assertInstanceOf(HasMany::class, $this->category->children());

        $childCategory = Category::factory()->create(['parent_id' => $this->category->id]);

        $this->assertTrue($this->category->children->contains($childCategory));
    }
    #[Test]
    public function it_has_many_products()
    {
        $this->assertInstanceOf(HasMany::class, $this->category->products());

        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->assertTrue($this->category->products->contains($product));
    }
    #[Test]
    public function active_scope_returns_only_active_categories()
    {
        Category::factory()->create(['active' => true]);
        Category::factory()->create(['active' => false]);
        Category::factory()->create(['active' => true]);

        $activeCategories = Category::active()->get();

        $this->assertEquals(3, $activeCategories->count()); // 2 + setUp category
        $this->assertTrue($activeCategories->every(fn($category) => $category->active === true));
    }
    #[Test]
    public function root_scope_returns_only_root_categories()
    {
        $parentCategory = Category::factory()->create(['parent_id' => null]);
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

        $rootCategories = Category::root()->get();

        $this->assertEquals(2, $rootCategories->count()); // parentCategory + setUp category
        $this->assertTrue($rootCategories->every(fn($category) => $category->parent_id === null));
    }
    #[Test]
    public function children_of_scope_returns_children_of_specific_category()
    {
        $child1 = Category::factory()->create(['parent_id' => $this->category->id]);
        $child2 = Category::factory()->create(['parent_id' => $this->category->id]);
        $otherChild = Category::factory()->create(['parent_id' => null]);

        $children = Category::childrenOf($this->category->id)->get();

        $this->assertEquals(2, $children->count());
        $this->assertTrue($children->every(fn($category) => $category->parent_id === $this->category->id));
    }
    #[Test]
    public function ordered_scope_returns_categories_ordered_by_order_field()
    {
        Category::factory()->create(['order' => 3]);
        Category::factory()->create(['order' => 1]);
        Category::factory()->create(['order' => 2]);

        $orderedCategories = Category::ordered()->get();

        $this->assertEquals(1, $orderedCategories->first()->order);
        $this->assertEquals(3, $orderedCategories->last()->order);
    }
    #[Test]
    public function get_ancestors_method_returns_parent_hierarchy()
    {
        $grandParent = Category::factory()->create(['parent_id' => null]);
        $parent = Category::factory()->create(['parent_id' => $grandParent->id]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $ancestors = $child->getAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertTrue($ancestors->contains($parent));
        $this->assertTrue($ancestors->contains($grandParent));
    }
    #[Test]
    public function get_descendants_method_returns_all_descendants()
    {
        $child1 = Category::factory()->create(['parent_id' => $this->category->id]);
        $child2 = Category::factory()->create(['parent_id' => $this->category->id]);
        $grandChild = Category::factory()->create(['parent_id' => $child1->id]);

        $descendants = $this->category->getDescendants();

        $this->assertCount(3, $descendants);
        $this->assertTrue($descendants->contains($child1));
        $this->assertTrue($descendants->contains($child2));
        $this->assertTrue($descendants->contains($grandChild));
    }
    #[Test]
    public function get_siblings_method_returns_categories_with_same_parent()
    {
        $parent = Category::factory()->create();
        $sibling1 = Category::factory()->create(['parent_id' => $parent->id]);
        $sibling2 = Category::factory()->create(['parent_id' => $parent->id]);
        $sibling3 = Category::factory()->create(['parent_id' => $parent->id]);
        $otherChild = Category::factory()->create(['parent_id' => null]);

        $siblings = $sibling1->getSiblings();

        $this->assertCount(2, $siblings); // Excludes itself
        $this->assertTrue($siblings->contains($sibling2));
        $this->assertTrue($siblings->contains($sibling3));
        $this->assertFalse($siblings->contains($sibling1));
        $this->assertFalse($siblings->contains($otherChild));
    }
    #[Test]
    public function full_name_attribute_includes_parent_names()
    {
        $parent = Category::factory()->create(['name' => 'Lubrificantes']);
        $child = Category::factory()->create([
            'name' => 'Óleo Motor',
            'parent_id' => $parent->id
        ]);

        $expectedFullName = 'Lubrificantes > Óleo Motor';
        $this->assertEquals($expectedFullName, $child->fullName);
    }
    #[Test]
    public function level_attribute_calculates_hierarchy_level()
    {
        $level1 = Category::factory()->create(['parent_id' => null]);
        $level2 = Category::factory()->create(['parent_id' => $level1->id]);
        $level3 = Category::factory()->create(['parent_id' => $level2->id]);

        $this->assertEquals(0, $level1->level);
        $this->assertEquals(1, $level2->level);
        $this->assertEquals(2, $level3->level);
    }
    #[Test]
    public function products_count_attribute_counts_category_products()
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        Product::factory()->count(2)->create(); // Other categories

        $this->assertEquals(3, $this->category->productsCount);
    }
    #[Test]
    public function is_root_method_checks_if_category_is_root()
    {
        $rootCategory = Category::factory()->create(['parent_id' => null]);
        $childCategory = Category::factory()->create(['parent_id' => $rootCategory->id]);

        $this->assertTrue($rootCategory->isRoot());
        $this->assertFalse($childCategory->isRoot());
    }
    #[Test]
    public function is_child_method_checks_if_category_has_parent()
    {
        $rootCategory = Category::factory()->create(['parent_id' => null]);
        $childCategory = Category::factory()->create(['parent_id' => $rootCategory->id]);

        $this->assertFalse($rootCategory->isChild());
        $this->assertTrue($childCategory->isChild());
    }
    #[Test]
    public function has_children_method_checks_if_category_has_children()
    {
        $parentCategory = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);
        $leafCategory = Category::factory()->create(['parent_id' => null]);

        $this->assertTrue($parentCategory->hasChildren());
        $this->assertFalse($leafCategory->hasChildren());
    }
    #[Test]
    public function factory_creates_category_with_valid_data()
    {
        $category = Category::factory()->create();

        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotNull($category->name);
        $this->assertIsInt($category->order);
        $this->assertTrue($category->active);
    }
    #[Test]
    public function it_uses_soft_deletes()
    {
        $categoryId = $this->category->id;

        $this->category->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
        $this->assertNotNull($this->category->fresh()->deleted_at);
    }
    #[Test]
    public function it_can_restore_soft_deleted_category()
    {
        $this->category->delete();
        $this->assertSoftDeleted('categories', ['id' => $this->category->id]);

        $this->category->restore();

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'deleted_at' => null
        ]);
    }
    #[Test]
    public function it_prevents_self_referencing_parent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Uma categoria não pode ser pai de si mesma');

        $this->category->update(['parent_id' => $this->category->id]);
    }
    #[Test]
    public function it_prevents_circular_references()
    {
        $child = Category::factory()->create(['parent_id' => $this->category->id]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Referência circular detectada');

        $this->category->update(['parent_id' => $child->id]);
    }
}
