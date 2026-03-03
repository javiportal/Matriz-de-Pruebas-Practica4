<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Todos los autenticados pueden ver préstamos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Solo estudiantes y docentes pueden solicitar préstamos.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['estudiante', 'docente']);
    }

    /**
     * Solo el dueño del préstamo puede devolverlo.
     */
    public function return(User $user, Loan $loan): bool
    {
        return $user->id === $loan->user_id;
    }
}