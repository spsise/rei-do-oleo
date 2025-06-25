<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Domain\User\Models\User;
use App\Domain\Service\Models\ServiceCenter;
use App\Domain\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected ServiceCenter $serviceCenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceCenter = ServiceCenter::factory()->create();
        $this->user = User::factory()
            ->withServiceCenter($this->serviceCenter)
            ->create();
    }
    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'name',
            'email',
            'password',
            'service_center_id',
            'phone',
            'whatsapp',
            'document',
            'birth_date',
            'hire_date',
            'salary',
            'commission_rate',
            'specialties',
            'active',
            'last_login_at'
        ];

        $this->assertEquals($fillable, $this->user->getFillable());
    }
    #[Test]
    public function it_has_correct_hidden_attributes()
    {
        $hidden = [
            'password',
            'remember_token',
        ];

        $this->assertEquals($hidden, $this->user->getHidden());
    }
    #[Test]
    public function it_has_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'hire_date' => 'date',
            'salary' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];

        foreach ($casts as $attribute => $cast) {
            $this->assertEquals($cast, $this->user->getCasts()[$attribute] ?? null);
        }
    }
    #[Test]
    public function it_belongs_to_service_center()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->user->serviceCenter());
        $this->assertInstanceOf(ServiceCenter::class, $this->user->serviceCenter);
        $this->assertEquals($this->serviceCenter->id, $this->user->service_center_id);
    }
    #[Test]
    public function it_has_many_technical_services()
    {
        $this->assertInstanceOf(HasMany::class, $this->user->technicalServices());

        $service = Service::factory()
            ->create(['technician_id' => $this->user->id]);

        $this->assertTrue($this->user->technicalServices->contains($service));
    }
    #[Test]
    public function it_has_many_attended_services()
    {
        $this->assertInstanceOf(HasMany::class, $this->user->attendedServices());

        $service = Service::factory()
            ->create(['attendant_id' => $this->user->id]);

        $this->assertTrue($this->user->attendedServices->contains($service));
    }
    #[Test]
    public function it_can_have_roles()
    {
        $role = Role::create(['name' => 'technician', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $this->assertTrue($this->user->hasRole('technician'));
        $this->assertTrue($this->user->roles->contains($role));
    }
    #[Test]
    public function active_scope_returns_only_active_users()
    {
        User::factory()->create(['active' => true]);
        User::factory()->create(['active' => false]);
        User::factory()->create(['active' => true]);

        $activeUsers = User::active()->get();

        $this->assertEquals(4, $activeUsers->count()); // 3 + setUp user
        $this->assertTrue($activeUsers->every(fn($user) => $user->active === true));
    }
    #[Test]
    public function by_service_center_scope_filters_by_service_center()
    {
        $anotherServiceCenter = ServiceCenter::factory()->create();

        User::factory(3)->withServiceCenter($this->serviceCenter)->create();
        User::factory(2)->withServiceCenter($anotherServiceCenter)->create();

        $users = User::byServiceCenter($this->serviceCenter->id)->get();

        $this->assertEquals(4, $users->count()); // 3 + setUp user
        $this->assertTrue($users->every(fn($user) => $user->service_center_id === $this->serviceCenter->id));
    }
    #[Test]
    public function by_role_scope_filters_by_role()
    {
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);

        $technician1 = User::factory()->create();
        $technician2 = User::factory()->create();
        $manager = User::factory()->create();

        $technician1->assignRole($technicianRole);
        $technician2->assignRole($technicianRole);
        $manager->assignRole($managerRole);

        $technicians = User::byRole('technician')->get();

        $this->assertEquals(2, $technicians->count());
        $this->assertTrue($technicians->every(fn($user) => $user->hasRole('technician')));
    }
    #[Test]
    public function is_technician_method_returns_correct_boolean()
    {
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);

        $this->user->assignRole($technicianRole);
        $this->assertTrue($this->user->isTechnician());

        $this->user->syncRoles([$managerRole]);
        $this->assertFalse($this->user->isTechnician());
    }
    #[Test]
    public function is_manager_method_returns_correct_boolean()
    {
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'web']);

        $this->user->assignRole($managerRole);
        $this->assertTrue($this->user->isManager());

        $this->user->syncRoles([$technicianRole]);
        $this->assertFalse($this->user->isManager());
    }
    #[Test]
    public function is_admin_method_returns_correct_boolean()
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'web']);

        $this->user->assignRole($adminRole);
        $this->assertTrue($this->user->isAdmin());

        $this->user->syncRoles([$technicianRole]);
        $this->assertFalse($this->user->isAdmin());
    }
    #[Test]
    public function it_validates_cpf_format()
    {
        $validCPF = $this->generateValidCPF();
        $this->user->update(['document' => $validCPF]);

        $this->assertEquals($validCPF, $this->user->document);
    }
    #[Test]
    public function it_formats_phone_numbers()
    {
        $phone = '11987654321';
        $this->user->update(['phone' => $phone]);

        $this->assertEquals($phone, $this->user->phone);
    }
    #[Test]
    public function it_hashes_password_on_creation()
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertTrue(Hash::check('plaintext', $user->password));
        $this->assertNotEquals('plaintext', $user->password);
    }
    #[Test]
    public function it_uses_soft_deletes()
    {
        $userId = $this->user->id;

        $this->user->delete();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNotNull($this->user->fresh()->deleted_at);
    }
    #[Test]
    public function it_can_restore_soft_deleted_user()
    {
        $this->user->delete();
        $this->assertSoftDeleted('users', ['id' => $this->user->id]);

        $this->user->restore();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'deleted_at' => null
        ]);
    }
    #[Test]
    public function factory_creates_user_with_valid_data()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->service_center_id);
        $this->assertTrue($user->active);
    }
    #[Test]
    public function factory_can_create_admin_user()
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertStringContainsString('Admin', $admin->name);
    }
    #[Test]
    public function factory_can_create_technician_user()
    {
        $technician = User::factory()->technician()->create();

        $this->assertTrue($technician->hasRole('technician'));
        $this->assertStringContainsString('Técnico', $technician->name);
        $this->assertNotNull($technician->specialties);
    }
    #[Test]
    public function factory_can_create_manager_user()
    {
        $manager = User::factory()->manager()->create();

        $this->assertTrue($manager->hasRole('manager'));
        $this->assertStringContainsString('Gerente', $manager->name);
    }
    #[Test]
    public function factory_can_create_attendant_user()
    {
        $attendant = User::factory()->attendant()->create();

        $this->assertTrue($attendant->hasRole('attendant'));
        $this->assertStringContainsString('Atendente', $attendant->name);
    }
    #[Test]
    public function it_updates_last_login_at_timestamp()
    {
        $now = Carbon::now();
        $this->user->update(['last_login_at' => $now]);

        $this->assertEquals($now->format('Y-m-d H:i:s'), $this->user->last_login_at->format('Y-m-d H:i:s'));
    }
    #[Test]
    public function it_calculates_age_from_birth_date()
    {
        $birthDate = Carbon::now()->subYears(30);
        $this->user->update(['birth_date' => $birthDate]);

        $this->assertEquals(30, $this->user->birth_date->age);
    }
    #[Test]
    public function it_calculates_experience_from_hire_date()
    {
        $hireDate = Carbon::now()->subYears(5);
        $this->user->update(['hire_date' => $hireDate]);

        $experienceYears = $this->user->hire_date->diffInYears(Carbon::now());
        $this->assertEquals(5, $experienceYears);
    }
    #[Test]
    public function senior_factory_state_creates_experienced_user()
    {
        $seniorUser = User::factory()->senior()->create();

        $experienceYears = $seniorUser->hire_date->diffInYears(Carbon::now());
        $this->assertGreaterThanOrEqual(5, $experienceYears);
        $this->assertGreaterThanOrEqual(4000, $seniorUser->salary);
    }
    #[Test]
    public function junior_factory_state_creates_new_user()
    {
        $juniorUser = User::factory()->junior()->create();

        $experienceYears = $juniorUser->hire_date->diffInYears(Carbon::now());
        $this->assertLessThanOrEqual(2, $experienceYears);
        $this->assertLessThanOrEqual(4000, $juniorUser->salary);
    }
    #[Test]
    public function inactive_factory_state_creates_inactive_user()
    {
        $inactiveUser = User::factory()->inactive()->create();

        $this->assertFalse($inactiveUser->active);
    }
    #[Test]
    public function active_factory_state_creates_active_user()
    {
        $activeUser = User::factory()->active()->create();

        $this->assertTrue($activeUser->active);
    }
    #[Test]
    public function it_has_proper_table_name()
    {
        $this->assertEquals('users', $this->user->getTable());
    }
    #[Test]
    public function it_has_proper_primary_key()
    {
        $this->assertEquals('id', $this->user->getKeyName());
        $this->assertTrue($this->user->getIncrementing());
    }
    #[Test]
    public function it_handles_null_values_properly()
    {
        $user = User::factory()->create([
            'phone' => null,
            'whatsapp' => null,
            'document' => null,
            'birth_date' => null,
            'salary' => null,
            'commission_rate' => null,
        ]);

        $this->assertNull($user->phone);
        $this->assertNull($user->whatsapp);
        $this->assertNull($user->document);
        $this->assertNull($user->birth_date);
        $this->assertNull($user->salary);
        $this->assertNull($user->commission_rate);
    }
    #[Test]
    public function it_validates_email_uniqueness()
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }
    #[Test]
    public function it_handles_decimal_precision_correctly()
    {
        $salary = 2500.75;
        $commissionRate = 12.5;

        $this->user->update([
            'salary' => $salary,
            'commission_rate' => $commissionRate
        ]);

        $this->assertEquals($salary, (float) $this->user->salary);
        $this->assertEquals($commissionRate, (float) $this->user->commission_rate);
    }
}
