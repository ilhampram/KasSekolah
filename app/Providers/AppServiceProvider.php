<?php

namespace App\Providers;

use App\Models\CashBill;
use App\Models\Payment;
use App\Policies\CashBillPolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(CashBill::class, CashBillPolicy::class);

        view()->composer('layouts.app', function ($view) {
            $user = auth()->user();
            if ($user && in_array($user->role, ['admin', 'bendahara_kelas'])) {
                $query = Payment::where('status', 'pending');
                if ($user->isBendaharaKelas() && $user->class_id) {
                    $query->whereHas('cashBill', fn ($qb) => $qb->where('class_id', $user->class_id));
                }
                $view->with('pendingPaymentsCount', $query->count());
            } else {
                $view->with('pendingPaymentsCount', 0);
            }
        });
    }
}
