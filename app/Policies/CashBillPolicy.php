<?php

namespace App\Policies;

use App\Models\CashBill;
use App\Models\User;

class CashBillPolicy
{
    /**
     * Determine whether the user can view the bill.
     */
    public function view(User $user, CashBill $bill): bool
    {
        if ($user->isAdmin() || $user->isBendaharaKelas()) {
            return true;
        }

        return $user->class_id !== null && $bill->class_id === $user->class_id;
    }

    /**
     * Determine whether the user can create bills.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isBendaharaKelas();
    }

    /**
     * Determine whether the user can update the bill.
     */
    public function update(User $user, CashBill $bill): bool
    {
        return $user->isAdmin() || $user->isBendaharaKelas();
    }

    /**
     * Determine whether the user can delete the bill.
     */
    public function delete(User $user, CashBill $bill): bool
    {
        return $user->isAdmin();
    }
}
