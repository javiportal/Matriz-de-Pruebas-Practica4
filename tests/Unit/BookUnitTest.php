<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_book_tiene_campos_fillable_correctos(): void
    {
        $book = new Book();
        $expected = ['title', 'description', 'ISBN', 'total_copies', 'available_copies', 'is_available'];

        $this->assertEquals($expected, $book->getFillable());
    }

    /** @test */
    public function test_book_tiene_relacion_has_many_loans(): void
    {
        $book = Book::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $book->loans()
        );
    }

    /** @test */
    public function test_is_available_retorna_true_con_stock(): void
    {
        $book = Book::factory()->create([
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $this->assertTrue($book->isAvailable());
    }

    /** @test */
    public function test_is_available_retorna_false_sin_stock(): void
    {
        $book = Book::factory()->unavailable()->create();

        $this->assertFalse($book->isAvailable());
    }

    /** @test */
public function book_becomes_unavailable_when_no_copies(): void
{
    $book = Book::factory()->create([
        'available_copies' => 0,
        'is_available' => false,
    ]);

    $this->assertFalse($book->is_available);
    $this->assertEquals(0, $book->available_copies);
}

/** @test */
public function book_becomes_available_after_return(): void
{
    $book = Book::factory()->create([
        'available_copies' => 0,
        'is_available' => false,
    ]);

    $book->update([
        'available_copies' => 1,
        'is_available' => true,
    ]);

    $this->assertTrue($book->fresh()->is_available);
    $this->assertEquals(1, $book->fresh()->available_copies);
}
}
