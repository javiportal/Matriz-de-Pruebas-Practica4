<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function loan_tiene_campos_fillable_correctos(): void
    {
        $loan = new Loan();
        $expected = ['user_id', 'requester_name', 'book_id', 'return_at'];

        $this->assertEquals($expected, $loan->getFillable());
    }

    /** @test */
    public function loan_pertenece_a_book(): void
    {
        $loan = Loan::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $loan->book()
        );
        $this->assertInstanceOf(Book::class, $loan->book);
    }

    /** @test */
    public function loan_pertenece_a_user(): void
    {
        $loan = Loan::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $loan->user()
        );
        $this->assertInstanceOf(User::class, $loan->user);
    }

    /** @test */
    public function is_returned_retorna_true_con_fecha_devolucion(): void
    {
        $loan = Loan::factory()->returned()->create();

        $this->assertTrue($loan->isReturned());
    }

    /** @test */
    public function is_returned_retorna_false_sin_fecha_devolucion(): void
    {
        $loan = Loan::factory()->create(['return_at' => null]);

        $this->assertFalse($loan->isReturned());
    }
}