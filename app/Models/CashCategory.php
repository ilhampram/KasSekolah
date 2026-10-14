<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    // ─── Relationships ────────────────────────────────────────

    /**
     * Cash bills under this category.
     */
    public function cashBills(): HasMany
    {
        return $this->hasMany(CashBill::class, 'category_id');
    }

    /**
     * Cash expenses under this category.
     */
    public function cashExpenses(): HasMany
    {
        return $this->hasMany(CashExpense::class, 'category_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isIncome(): bool
    {
        return $this->type === 'in';
    }

    public function isExpense(): bool
    {
        return $this->type === 'out';
    }
}
