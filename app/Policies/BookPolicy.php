<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Todos los autenticados pueden ver libros.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Book $book): bool
    {
        return true;
    }

    /**
     * Solo el bibliotecario puede crear libros.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('bibliotecario');
    }

    /**
     * Solo el bibliotecario puede actualizar libros.
     */
    public function update(User $user, Book $book): bool
    {
        return $user->hasRole('bibliotecario');
    }

    /**
     * Solo el bibliotecario puede eliminar libros.
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->hasRole('bibliotecario');
    }
}