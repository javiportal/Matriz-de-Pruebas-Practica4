<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private User $bibliotecario;
    private User $estudiante;
    private User $docente;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'bibliotecario', 'guard_name' => 'web']);
        Role::create(['name' => 'estudiante', 'guard_name' => 'web']);
        Role::create(['name' => 'docente', 'guard_name' => 'web']);

        $this->bibliotecario = User::factory()->create();
        $this->bibliotecario->assignRole('bibliotecario');

        $this->estudiante = User::factory()->create();
        $this->estudiante->assignRole('estudiante');

        $this->docente = User::factory()->create();
        $this->docente->assignRole('docente');
    }

    // ==================== PRESTAR ====================

    /** @test */
    public function test_estudiante_puede_prestar_libro(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => $this->estudiante->name,
                'book_id' => $book->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->estudiante->id,
            'book_id' => $book->id,
        ]);

        $this->assertEquals(4, $book->fresh()->available_copies);
    }

    /** @test */
    public function test_docente_puede_prestar_libro(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 3,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->docente, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => $this->docente->name,
                'book_id' => $book->id,
            ]);

        $response->assertStatus(201);
        $this->assertEquals(2, $book->fresh()->available_copies);
    }

    /** @test */
    public function test_bibliotecario_no_puede_prestar_libro(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => $this->bibliotecario->name,
                'book_id' => $book->id,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_no_puede_prestar_libro_sin_stock(): void
    {
        $book = Book::factory()->unavailable()->create();

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => $this->estudiante->name,
                'book_id' => $book->id,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_no_puede_prestar_libro_inexistente(): void
    {
        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson('/api/v1/loans', [
                'requester_name' => $this->estudiante->name,
                'book_id' => 9999,
            ]);

        $response->assertStatus(422); // 422 por la validación exists:books,id
    }

    /** @test */
    public function test_prestar_sin_autenticacion(): void
    {
        $response = $this->postJson('/api/v1/loans', [
            'requester_name' => 'Test',
            'book_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    // ==================== DEVOLVER ====================

    /** @test */
    public function test_devolver_libro_correctamente(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 4,
            'is_available' => true,
        ]);

        $loan = Loan::factory()->create([
            'user_id' => $this->estudiante->id,
            'book_id' => $book->id,
            'return_at' => null,
        ]);

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200);
        $this->assertNotNull($loan->fresh()->return_at);
        $this->assertEquals(5, $book->fresh()->available_copies);
    }

    /** @test */
    public function test_no_puede_devolver_prestamo_ya_devuelto(): void
    {
        $loan = Loan::factory()->returned()->create([
            'user_id' => $this->estudiante->id,
        ]);

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(422);
    }

    /** @test */
    public function test_no_puede_devolver_prestamo_ajeno(): void
    {
        $otroEstudiante = User::factory()->create();
        $otroEstudiante->assignRole('estudiante');

        $loan = Loan::factory()->create([
            'user_id' => $otroEstudiante->id,
            'return_at' => null,
        ]);

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(403);
    }

    // ==================== HISTORIAL ====================

    /** @test */
    public function test_estudiante_ve_solo_sus_prestamos(): void
    {
        Loan::factory()->count(2)->create([
            'user_id' => $this->estudiante->id,
        ]);
        Loan::factory()->count(3)->create(); // De otros usuarios

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->getJson('/api/v1/loans');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    /** @test */
    public function test_bibliotecario_ve_todos_los_prestamos(): void
    {
        Loan::factory()->count(5)->create();

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->getJson('/api/v1/loans');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    /** @test */
    public function test_historial_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/v1/loans');

        $response->assertStatus(401);
    }
}
