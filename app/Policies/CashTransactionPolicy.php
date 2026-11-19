<?php

namespace App\Policies;

use App\Models\CashTransaction;
use App\Models\User;

class CashTransactionPolicy
{
    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || ($user->isBendaharaKelas() && $user->class_id !== null);
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, CashTransaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBendaharaKelas()) {
            return $user->class_id !== null && $user->class_id === $transaction->class_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->isBendaharaKelas() && $user->class_id !== null);
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, CashTransaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBendaharaKelas()) {
            return $user->class_id !== null && $user->class_id === $transaction->class_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete/void the transaction.
     */
    public function delete(User $user, CashTransaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBendaharaKelas()) {
            return $user->class_id !== null && $user->class_id === $transaction->class_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view the transaction proof.
     */
    public function proof(User $user, CashTransaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isBendaharaKelas()) {
            return $user->class_id !== null && $user->class_id === $transaction->class_id;
        }

        if ($user->isSiswa()) {
            if ($user->class_id === null || $user->class_id !== $transaction->class_id) {
                return false;
            }

            if ($transaction->payment_id) {
                return $transaction->payment?->student_id === $user->id;
            }

            return true;
        }

        return false;
    }
}
