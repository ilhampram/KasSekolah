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
    }
}
