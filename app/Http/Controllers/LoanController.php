<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Historial de préstamos.
     * Bibliotecario ve todos, estudiante/docente solo los suyos.
     */
    public function index()
    {
        $this->authorize('viewAny', Loan::class);

        $user = Auth::user();

        if ($user->hasRole('bibliotecario')) {
            $loans = Loan::with('book')->paginate();
        } else {
            $loans = $user->loans()->with('book')->paginate();
        }

        return LoanResource::collection($loans);
    }

    public function store(StoreLoanRequest $request)
    {
        $this->authorize('create', Loan::class);

        $book = Book::find($request->input('book_id'));

        if (! $book->is_available || $book->available_copies === 0) {
            return response()->json(['message' => 'Book is not available'], 422);
        }

        $loan = Loan::create([
            'user_id' => Auth::id(),
            'requester_name' => $request->input('requester_name'),
            'book_id' => $request->input('book_id'),
        ]);

        $book->update([
            'available_copies' => $book->available_copies - 1,
            'is_available' => $book->available_copies - 1 > 0,
        ]);

        return response()->json($loan, 201);
    }
}
