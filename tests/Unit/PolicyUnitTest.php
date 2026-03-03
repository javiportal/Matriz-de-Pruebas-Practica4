<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\BookPolicy;
use App\Policies\LoanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PolicyUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'bibliotecario', 'guard_name' => 'web']);
        Role::create(['name' => 'estudiante', 'guard_name' => 'web']);
        Role::create(['name' => 'docente', 'guard_name' => 'web']);
    }

    /** @test */
    public function test_user_tiene_trait_has_roles(): void
    {
        $this->assertTrue(
            in_array(
                \Spatie\Permission\Traits\HasRoles::class,
                class_uses_recursive(User::class)
            )
        );
    }

    /** @test */
    public function test_bibliotecario_puede_crear_libros(): void
    {
        $user = User::factory()->create();
        $user->assignRole('bibliotecario');

        $policy = new BookPolicy();
        $this->assertTrue($policy->create($user));
    }

    /** @test */
    public function test_estudiante_no_puede_crear_libros(): void
    {
        $user = User::factory()->create();
        $user->assignRole('estudiante');

        $policy = new BookPolicy();
        $this->assertFalse($policy->create($user));
    }

    /** @test */
    public function test_estudiante_puede_prestar_libros(): void
    {
        $user = User::factory()->create();
        $user->assignRole('estudiante');

        $policy = new LoanPolicy();
        $this->assertTrue($policy->create($user));
    }

    /** @test */
    public function test_bibliotecario_no_puede_prestar_libros(): void
    {
        $user = User::factory()->create();
        $user->assignRole('bibliotecario');

        $policy = new LoanPolicy();
        $this->assertFalse($policy->create($user));
    }
}
