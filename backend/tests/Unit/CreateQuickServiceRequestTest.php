<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\Api\Service\CreateQuickServiceRequest;
use App\Domain\Client\Models\Client;
use App\Domain\Client\Models\Vehicle;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceTemplate;
use App\Domain\Service\Models\ServiceCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class CreateQuickServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    private CreateQuickServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new CreateQuickServiceRequest();
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('client_id', $rules);
        $this->assertArrayHasKey('vehicle_id', $rules);
        $this->assertArrayHasKey('description', $rules);

        $this->assertContains('required', $rules['client_id']);
        $this->assertContains('required', $rules['vehicle_id']);
        $this->assertContains('required', $rules['description']);
    }

    /** @test */
    public function it_validates_client_id_exists()
    {
        $data = [
            'client_id' => 999,
            'vehicle_id' => 1,
            'description' => 'Test service'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('client_id'));
    }

    /** @test */
    public function it_validates_vehicle_id_exists()
    {
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => 999,
            'description' => 'Test service'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('vehicle_id'));
    }

    /** @test */
    public function it_validates_description_length()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        // Test minimum length
        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'ab'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('description'));

        // Test maximum length
        $data['description'] = str_repeat('a', 501);

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('description'));
    }

    /** @test */
    public function it_validates_estimated_duration_limits()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        // Test minimum duration
        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service',
            'estimated_duration' => 10
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('estimated_duration'));

        // Test maximum duration
        $data['estimated_duration'] = 500;

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('estimated_duration'));
    }

    /** @test */
    public function it_validates_priority_values()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service',
            'priority' => 'invalid_priority'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('priority'));
    }

    /** @test */
    public function it_accepts_valid_priority_values()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        $validPriorities = ['low', 'medium', 'high'];

        foreach ($validPriorities as $priority) {
            $data = [
                'client_id' => $client->id,
                'vehicle_id' => $vehicle->id,
                'description' => 'Test service',
                'priority' => $priority
            ];

            $validator = Validator::make($data, $this->request->rules());
            $validator->setContainer($this->app);

            $this->assertFalse($validator->fails(), "Priority '$priority' should be valid");
        }
    }

    /** @test */
    public function it_validates_template_id_exists()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service',
            'template_id' => 999
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('template_id'));
    }

    /** @test */
    public function it_validates_client_vehicle_relationship()
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client2->id]);

        $data = [
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        // Manually trigger the custom validation
        $this->request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('vehicle_id'));
    }

    /** @test */
    public function it_validates_user_service_center_access()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);

        // Create user without service center
        $user = User::factory()->create(['service_center_id' => null]);
        $this->actingAs($user);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Test service'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        // Manually trigger the custom validation
        $this->request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('service_center'));
    }

    /** @test */
    public function it_accepts_valid_data()
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create(['client_id' => $client->id]);
        $serviceCenter = ServiceCenter::factory()->create();
        $user = User::factory()->create(['service_center_id' => $serviceCenter->id]);
        $this->actingAs($user);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'description' => 'Valid service description',
            'estimated_duration' => 60,
            'priority' => 'medium',
            'notes' => 'Test notes'
        ];

        $validator = Validator::make($data, $this->request->rules());
        $validator->setContainer($this->app);

        // Manually trigger the custom validation
        $this->request->withValidator($validator);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_has_custom_error_messages()
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('client_id.required', $messages);
        $this->assertArrayHasKey('vehicle_id.required', $messages);
        $this->assertArrayHasKey('description.required', $messages);
        $this->assertArrayHasKey('estimated_duration.min', $messages);
        $this->assertArrayHasKey('priority.in', $messages);
    }

    /** @test */
    public function it_has_custom_attributes()
    {
        $attributes = $this->request->attributes();

        $this->assertArrayHasKey('client_id', $attributes);
        $this->assertArrayHasKey('vehicle_id', $attributes);
        $this->assertArrayHasKey('description', $attributes);
        $this->assertArrayHasKey('estimated_duration', $attributes);
        $this->assertArrayHasKey('priority', $attributes);
    }
}
