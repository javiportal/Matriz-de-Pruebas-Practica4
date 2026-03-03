<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function book_tiene_campos_fillable_correctos(): void
    {
        $book = new Book();
        $expected = ['title', 'description', 'ISBN', 'total_copies', 'available_copies', 'is_available'];

        $this->assertEquals($expected, $book->getFillable());
    }

    /** @test */
    public function book_tiene_relacion_has_many_loans(): void
    {
        $book = Book::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $book->loans()
        );
    }

    /** @test */
    public function is_available_retorna_true_con_stock(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $this->assertTrue($book->isAvailable());
    }

    /** @test */
    public function is_available_retorna_false_sin_stock(): void
    {
        $book = Book::factory()->unavailable()->create();

        $this->assertFalse($book->isAvailable());
    }
}