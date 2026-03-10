<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookTest extends TestCase
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


   
    public function test_listar_libros_autenticado(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->getJson('/api/v1/books');

        $response->assertStatus(200);
    }

  
    public function test_listar_libros_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(401);
    }

    
    
    public function test_ver_detalle_libro_existente(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $book->title]);
    }

   
    public function test_ver_detalle_libro_inexistente(): void
    {
        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->getJson('/api/v1/books/9999');

        $response->assertStatus(404);
    }

    
    public function test_crear_libro_como_bibliotecario(): void
    {
        $data = [
            'title' => 'Clean Code',
            'description' => 'Libro sobre buenas prácticas de programación',
            'ISBN' => '9780132350884',
            'total_copies' => 5,
            'available_copies' => 5,
        ];

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Clean Code']);

        $this->assertDatabaseHas('books', ['ISBN' => '9780132350884']);
    }

   
    public function test_crear_libro_como_estudiante_retorna_403(): void
    {
        $data = [
            'title' => 'Test',
            'description' => 'Desc',
            'ISBN' => '1234567890123',
            'total_copies' => 5,
            'available_copies' => 5,
        ];

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(403);
    }

   
    public function test_crear_libro_como_docente_retorna_403(): void
    {
        $data = [
            'title' => 'Test',
            'description' => 'Desc',
            'ISBN' => '1234567890123',
            'total_copies' => 5,
            'available_copies' => 5,
        ];

        $response = $this->actingAs($this->docente, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(403);
    }

   
    public function test_crear_libro_con_campos_faltantes(): void
    {
        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->postJson('/api/v1/books', ['title' => '']);

        $response->assertStatus(422);
    }

    
    public function test_crear_libro_con_isbn_duplicado(): void
    {
        Book::factory()->create(['ISBN' => '1234567890123']);

        $data = [
            'title' => 'Otro libro',
            'description' => 'Desc',
            'ISBN' => '1234567890123',
            'total_copies' => 1,
            'available_copies' => 1,
        ];

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
    }

    
    public function test_actualizar_libro_como_bibliotecario(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => 'Título Actualizado',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Título Actualizado']);
    }

    public function test_actualizar_libro_como_estudiante_retorna_403(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->estudiante, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => 'Hack',
            ]);

        $response->assertStatus(403);
    }

   
    public function test_actualizar_libro_inexistente(): void
    {
        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->putJson('/api/v1/books/9999', [
                'title' => 'Nada',
            ]);

        $response->assertStatus(404);
    }


    public function test_eliminar_libro_como_bibliotecario(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

  
    public function test_eliminar_libro_como_docente_retorna_403(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->docente, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403);
    }


    public function test_eliminar_libro_inexistente(): void
    {
        $response = $this->actingAs($this->bibliotecario, 'sanctum')
            ->deleteJson('/api/v1/books/9999');

        $response->assertStatus(404);
    }
}
