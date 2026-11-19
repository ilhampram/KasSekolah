<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->index(['class_id', 'transaction_date', 'type'], 'trx_class_date_type_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['cash_bill_id', 'student_id', 'status'], 'payments_bill_student_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['class_id', 'role'], 'users_class_role_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropIndex('trx_class_date_type_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_bill_student_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_class_role_idx');
        });
    }
};
