<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'bibliotecario', 'guard_name' => 'web']);
        Role::create(['name' => 'estudiante', 'guard_name' => 'web']);
        Role::create(['name' => 'docente', 'guard_name' => 'web']);
    }

    public function test_login_exitoso_con_credenciales_validas(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_login_fallido_credenciales_invalidas(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fallido_campos_vacios(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fallido_email_formato_invalido(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }



    public function test_logout_exitoso_con_token_valido(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully']);
    }

    public function test_logout_fallido_sin_autenticacion(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    public function test_obtener_perfil_con_token_valido(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', $user->email);
    }


    public function test_perfil_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }
}
