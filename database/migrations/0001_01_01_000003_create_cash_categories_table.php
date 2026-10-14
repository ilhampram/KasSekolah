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
        Schema::create('cash_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // e.g., "Uang Kas Mingguan", "Uang Acara"
            $table->enum('type', ['in', 'out']);     // in = pemasukan, out = pengeluaran
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_categories');
    }
};
