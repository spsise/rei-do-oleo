<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Service\Repositories\ServiceTemplateRepository;
use App\Domain\Service\Models\ServiceTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class ServiceTemplateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ServiceTemplateRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ServiceTemplateRepository();
    }

    /** @test */
    public function it_can_get_all_templates()
    {
        ServiceTemplate::factory()->count(3)->create();

        $templates = $this->repository->getAll();

        $this->assertCount(3, $templates);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $templates);
    }

    /** @test */
    public function it_can_get_active_templates()
    {
        ServiceTemplate::factory()->count(2)->create(['active' => true]);
        ServiceTemplate::factory()->count(1)->create(['active' => false]);

        $templates = $this->repository->getActive();

        $this->assertCount(2, $templates);
        $this->assertTrue($templates->every(fn($template) => $template->active));
    }

    /** @test */
    public function it_can_get_active_templates_by_category()
    {
        ServiceTemplate::factory()->count(2)->create([
            'category' => 'maintenance',
            'active' => true
        ]);

        ServiceTemplate::factory()->count(1)->create([
            'category' => 'repair',
            'active' => true
        ]);

        ServiceTemplate::factory()->count(1)->create([
            'category' => 'maintenance',
            'active' => false
        ]);

        $templates = $this->repository->getActive('maintenance');

        $this->assertCount(2, $templates);
        $this->assertTrue($templates->every(fn($template) =>
            $template->active && $template->category === 'maintenance'
        ));
    }

    /** @test */
    public function it_can_find_template_by_id()
    {
        $template = ServiceTemplate::factory()->create();

        $found = $this->repository->find($template->id);

        $this->assertNotNull($found);
        $this->assertEquals($template->id, $found->id);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_id()
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_find_template_by_name()
    {
        $template = ServiceTemplate::factory()->create([
            'name' => 'Test Template'
        ]);

        $found = $this->repository->findByName('Test Template');

        $this->assertNotNull($found);
        $this->assertEquals('Test Template', $found->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_name()
    {
        $found = $this->repository->findByName('Nonexistent Template');

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_create_template()
    {
        $data = [
            'name' => 'New Template',
            'description' => 'New description',
            'category' => 'maintenance',
            'estimated_duration' => 60,
            'priority' => 'medium',
            'active' => true
        ];

        $template = $this->repository->create($data);

        $this->assertInstanceOf(ServiceTemplate::class, $template);
        $this->assertEquals('New Template', $template->name);
        $this->assertEquals('maintenance', $template->category);

        $this->assertDatabaseHas('service_templates', [
            'name' => 'New Template',
            'category' => 'maintenance'
        ]);
    }

    /** @test */
    public function it_can_update_template()
    {
        $template = ServiceTemplate::factory()->create([
            'name' => 'Old Name',
            'category' => 'general'
        ]);

        $data = [
            'name' => 'Updated Name',
            'category' => 'maintenance'
        ];

        $updated = $this->repository->update($template, $data);

        $this->assertInstanceOf(ServiceTemplate::class, $updated);
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('maintenance', $updated->category);

        $this->assertDatabaseHas('service_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
            'category' => 'maintenance'
        ]);
    }

    /** @test */
    public function it_can_delete_template()
    {
        $template = ServiceTemplate::factory()->create();

        $deleted = $this->repository->delete($template);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('service_templates', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function it_can_get_templates_by_category()
    {
        ServiceTemplate::factory()->count(3)->create([
            'category' => 'maintenance'
        ]);

        ServiceTemplate::factory()->count(2)->create([
            'category' => 'repair'
        ]);

        $maintenanceTemplates = $this->repository->getByCategory('maintenance');

        $this->assertCount(3, $maintenanceTemplates);
        $this->assertTrue($maintenanceTemplates->every(fn($template) =>
            $template->category === 'maintenance'
        ));
    }

    /** @test */
    public function it_can_get_templates_by_priority()
    {
        ServiceTemplate::factory()->count(2)->create([
            'priority' => 'high'
        ]);

        ServiceTemplate::factory()->count(1)->create([
            'priority' => 'medium'
        ]);

        $highPriorityTemplates = $this->repository->getByPriority('high');

        $this->assertCount(2, $highPriorityTemplates);
        $this->assertTrue($highPriorityTemplates->every(fn($template) =>
            $template->priority === 'high'
        ));
    }

    /** @test */
    public function it_can_search_templates()
    {
        ServiceTemplate::factory()->create([
            'name' => 'Oil Change Template',
            'description' => 'Template for oil change'
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Brake Service',
            'description' => 'Template for brake service'
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Tire Rotation',
            'description' => 'Template for tire rotation'
        ]);

        $results = $this->repository->search('oil');

        $this->assertCount(1, $results);
        $this->assertEquals('Oil Change Template', $results->first()->name);
    }

    /** @test */
    public function it_can_search_templates_by_description()
    {
        ServiceTemplate::factory()->create([
            'name' => 'Service A',
            'description' => 'Oil change and filter replacement'
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Service B',
            'description' => 'Brake inspection and repair'
        ]);

        $results = $this->repository->search('filter');

        $this->assertCount(1, $results);
        $this->assertEquals('Service A', $results->first()->name);
    }

    /** @test */
    public function it_returns_empty_collection_for_no_search_results()
    {
        ServiceTemplate::factory()->create([
            'name' => 'Oil Change',
            'description' => 'Oil change service'
        ]);

        $results = $this->repository->search('nonexistent');

        $this->assertCount(0, $results);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    /** @test */
    public function it_can_get_popular_templates()
    {
        // Create templates with different usage counts
        ServiceTemplate::factory()->create([
            'name' => 'Popular Template',
            'usage_count' => 100
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Less Popular Template',
            'usage_count' => 50
        ]);

        ServiceTemplate::factory()->create([
            'name' => 'Unpopular Template',
            'usage_count' => 10
        ]);

        $popularTemplates = $this->repository->getPopular(2);

        $this->assertCount(2, $popularTemplates);
        $this->assertEquals('Popular Template', $popularTemplates->first()->name);
        $this->assertEquals('Less Popular Template', $popularTemplates->last()->name);
    }

    /** @test */
    public function it_can_increment_usage_count()
    {
        $template = ServiceTemplate::factory()->create([
            'usage_count' => 5
        ]);

        $this->repository->incrementUsage($template);

        $template->refresh();

        $this->assertEquals(6, $template->usage_count);
    }

    /** @test */
    public function it_can_get_templates_with_pagination()
    {
        ServiceTemplate::factory()->count(15)->create();

        $paginated = $this->repository->getPaginated(10, 1);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $paginated);
        $this->assertEquals(10, $paginated->count());
        $this->assertEquals(15, $paginated->total());
    }

    /** @test */
    public function it_can_get_templates_by_multiple_categories()
    {
        ServiceTemplate::factory()->count(2)->create([
            'category' => 'maintenance'
        ]);

        ServiceTemplate::factory()->count(3)->create([
            'category' => 'repair'
        ]);

        ServiceTemplate::factory()->count(1)->create([
            'category' => 'inspection'
        ]);

        $templates = $this->repository->getByCategories(['maintenance', 'repair']);

        $this->assertCount(5, $templates);
        $this->assertTrue($templates->every(fn($template) =>
            in_array($template->category, ['maintenance', 'repair'])
        ));
    }

    /** @test */
    public function it_can_get_templates_by_duration_range()
    {
        ServiceTemplate::factory()->create([
            'estimated_duration' => 30
        ]);

        ServiceTemplate::factory()->create([
            'estimated_duration' => 60
        ]);

        ServiceTemplate::factory()->create([
            'estimated_duration' => 120
        ]);

        $templates = $this->repository->getByDurationRange(45, 90);

        $this->assertCount(1, $templates);
        $this->assertEquals(60, $templates->first()->estimated_duration);
    }

    /** @test */
    public function it_can_get_recently_created_templates()
    {
        $oldTemplate = ServiceTemplate::factory()->create([
            'created_at' => now()->subDays(10)
        ]);

        $recentTemplate = ServiceTemplate::factory()->create([
            'created_at' => now()->subDays(2)
        ]);

        $templates = $this->repository->getRecent(5);

        $this->assertCount(2, $templates);
        $this->assertEquals($recentTemplate->id, $templates->first()->id);
        $this->assertEquals($oldTemplate->id, $templates->last()->id);
    }

    /** @test */
    public function it_can_get_templates_with_service_items()
    {
        $template = ServiceTemplate::factory()->create([
            'service_items' => [
                [
                    'product_name' => 'Oil Filter',
                    'quantity' => 1,
                    'unit_price' => 25.00
                ]
            ]
        ]);

        $templates = $this->repository->getWithServiceItems();

        $this->assertCount(1, $templates);
        $this->assertNotNull($templates->first()->service_items);
        $this->assertCount(1, $templates->first()->service_items);
    }
}
