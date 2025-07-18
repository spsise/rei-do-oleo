<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Service\Models\ServiceTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class ServiceTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_service_template()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'category' => 'maintenance',
            'estimated_duration' => 60,
            'priority' => 'medium',
            'notes' => 'Test notes',
            'active' => true,
            'sort_order' => 1
        ]);

        $this->assertDatabaseHas('service_templates', [
            'name' => 'Test Template',
            'category' => 'maintenance',
            'priority' => 'medium'
        ]);

        $this->assertEquals('Test Template', $template->name);
        $this->assertEquals('maintenance', $template->category);
        $this->assertEquals('medium', $template->priority);
    }

    /** @test */
    public function it_has_default_values()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description'
        ]);

        $this->assertEquals('general', $template->category);
        $this->assertEquals('medium', $template->priority);
        $this->assertTrue($template->active);
        $this->assertEquals(0, $template->sort_order);
    }

    /** @test */
    public function it_can_scope_active_templates()
    {
        ServiceTemplate::create([
            'name' => 'Active Template',
            'description' => 'Active description',
            'active' => true
        ]);

        ServiceTemplate::create([
            'name' => 'Inactive Template',
            'description' => 'Inactive description',
            'active' => false
        ]);

        $activeTemplates = ServiceTemplate::active()->get();

        $this->assertCount(1, $activeTemplates);
        $this->assertEquals('Active Template', $activeTemplates->first()->name);
    }

    /** @test */
    public function it_can_scope_by_category()
    {
        ServiceTemplate::create([
            'name' => 'Maintenance Template',
            'description' => 'Maintenance description',
            'category' => 'maintenance'
        ]);

        ServiceTemplate::create([
            'name' => 'Repair Template',
            'description' => 'Repair description',
            'category' => 'repair'
        ]);

        $maintenanceTemplates = ServiceTemplate::byCategory('maintenance')->get();

        $this->assertCount(1, $maintenanceTemplates);
        $this->assertEquals('Maintenance Template', $maintenanceTemplates->first()->name);
    }

    /** @test */
    public function it_can_scope_ordered()
    {
        ServiceTemplate::create([
            'name' => 'Second Template',
            'description' => 'Second description',
            'sort_order' => 2
        ]);

        ServiceTemplate::create([
            'name' => 'First Template',
            'description' => 'First description',
            'sort_order' => 1
        ]);

        $orderedTemplates = ServiceTemplate::ordered()->get();

        $this->assertEquals('First Template', $orderedTemplates->first()->name);
        $this->assertEquals('Second Template', $orderedTemplates->last()->name);
    }

    /** @test */
    public function it_can_get_active_cached_templates()
    {
        ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'active' => true
        ]);

        $templates = ServiceTemplate::getActiveCached();

        $this->assertCount(1, $templates);
        $this->assertEquals('Test Template', $templates->first()->name);

        // Should be cached
        $this->assertTrue(Cache::has('service_templates'));
    }

    /** @test */
    public function it_can_get_active_cached_templates_by_category()
    {
        ServiceTemplate::create([
            'name' => 'Maintenance Template',
            'description' => 'Maintenance description',
            'category' => 'maintenance',
            'active' => true
        ]);

        ServiceTemplate::create([
            'name' => 'Repair Template',
            'description' => 'Repair description',
            'category' => 'repair',
            'active' => true
        ]);

        $maintenanceTemplates = ServiceTemplate::getActiveCached('maintenance');

        $this->assertCount(1, $maintenanceTemplates);
        $this->assertEquals('Maintenance Template', $maintenanceTemplates->first()->name);

        // Should be cached
        $this->assertTrue(Cache::has('service_templates_maintenance'));
    }

    /** @test */
    public function it_can_find_template_by_name()
    {
        ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description'
        ]);

        $template = ServiceTemplate::findByName('Test Template');

        $this->assertNotNull($template);
        $this->assertEquals('Test Template', $template->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_template_name()
    {
        $template = ServiceTemplate::findByName('Nonexistent Template');

        $this->assertNull($template);
    }

    /** @test */
    public function it_can_get_templates_by_category()
    {
        ServiceTemplate::create([
            'name' => 'Maintenance Template',
            'description' => 'Maintenance description',
            'category' => 'maintenance',
            'active' => true
        ]);

        ServiceTemplate::create([
            'name' => 'Repair Template',
            'description' => 'Repair description',
            'category' => 'repair',
            'active' => true
        ]);

        $maintenanceTemplates = ServiceTemplate::getByCategory('maintenance');

        $this->assertCount(1, $maintenanceTemplates);
        $this->assertEquals('Maintenance Template', $maintenanceTemplates->first()->name);
    }

    /** @test */
    public function it_formats_duration_correctly()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'estimated_duration' => 90
        ]);

        $this->assertEquals('1h 30min', $template->formatted_duration);
    }

    /** @test */
    public function it_formats_duration_with_only_minutes()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'estimated_duration' => 45
        ]);

        $this->assertEquals('45min', $template->formatted_duration);
    }

    /** @test */
    public function it_returns_na_for_null_duration()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'estimated_duration' => null
        ]);

        $this->assertEquals('N/A', $template->formatted_duration);
    }

    /** @test */
    public function it_returns_correct_priority_labels()
    {
        $lowTemplate = ServiceTemplate::create([
            'name' => 'Low Template',
            'description' => 'Low description',
            'priority' => 'low'
        ]);

        $mediumTemplate = ServiceTemplate::create([
            'name' => 'Medium Template',
            'description' => 'Medium description',
            'priority' => 'medium'
        ]);

        $highTemplate = ServiceTemplate::create([
            'name' => 'High Template',
            'description' => 'High description',
            'priority' => 'high'
        ]);

        $this->assertEquals('Baixa', $lowTemplate->priority_label);
        $this->assertEquals('Média', $mediumTemplate->priority_label);
        $this->assertEquals('Alta', $highTemplate->priority_label);
    }

    /** @test */
    public function it_returns_correct_category_labels()
    {
        $maintenanceTemplate = ServiceTemplate::create([
            'name' => 'Maintenance Template',
            'description' => 'Maintenance description',
            'category' => 'maintenance'
        ]);

        $repairTemplate = ServiceTemplate::create([
            'name' => 'Repair Template',
            'description' => 'Repair description',
            'category' => 'repair'
        ]);

        $inspectionTemplate = ServiceTemplate::create([
            'name' => 'Inspection Template',
            'description' => 'Inspection description',
            'category' => 'inspection'
        ]);

        $this->assertEquals('Manutenção', $maintenanceTemplate->category_label);
        $this->assertEquals('Reparo', $repairTemplate->category_label);
        $this->assertEquals('Inspeção', $inspectionTemplate->category_label);
    }

    /** @test */
    public function it_clears_cache_on_save()
    {
        // Create template to populate cache
        ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description'
        ]);

        // Verify cache exists
        $this->assertTrue(Cache::has('service_templates'));

        // Update template
        $template = ServiceTemplate::first();
        $template->update(['name' => 'Updated Template']);

        // Cache should be cleared
        $this->assertFalse(Cache::has('service_templates'));
    }

    /** @test */
    public function it_clears_cache_on_delete()
    {
        // Create template to populate cache
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description'
        ]);

        // Verify cache exists
        $this->assertTrue(Cache::has('service_templates'));

        // Delete template
        $template->delete();

        // Cache should be cleared
        $this->assertFalse(Cache::has('service_templates'));
    }

    /** @test */
    public function it_can_store_service_items_as_json()
    {
        $serviceItems = [
            [
                'product_name' => 'Óleo Motor',
                'quantity' => 1,
                'unit_price' => 89.90,
                'notes' => 'Óleo sintético'
            ],
            [
                'product_name' => 'Filtro de Óleo',
                'quantity' => 1,
                'unit_price' => 25.00,
                'notes' => 'Filtro de qualidade'
            ]
        ];

        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'service_items' => $serviceItems
        ]);

        $this->assertIsArray($template->service_items);
        $this->assertCount(2, $template->service_items);
        $this->assertEquals('Óleo Motor', $template->service_items[0]['product_name']);
    }

    /** @test */
    public function it_can_handle_null_service_items()
    {
        $template = ServiceTemplate::create([
            'name' => 'Test Template',
            'description' => 'Test description',
            'service_items' => null
        ]);

        $this->assertNull($template->service_items);
    }
}
