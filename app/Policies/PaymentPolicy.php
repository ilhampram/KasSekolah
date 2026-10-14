<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin() || $user->isBendaharaKelas()) {
            return true;
        }

        return $payment->student_id === $user->id;
    }

    /**
     * Determine whether the user can print/view receipt.
     */
    public function receipt(User $user, Payment $payment): bool
    {
        if ($user->isAdmin() || $user->isBendaharaKelas()) {
            return true;
        }

        return $payment->student_id === $user->id && $payment->status === 'verified';
    }

    /**
     * Determine whether the user can create payments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can verify or reject the payment.
     */
    public function verify(User $user, Payment $payment): bool
    {
        return $user->isAdmin() || $user->isBendaharaKelas();
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }
}
