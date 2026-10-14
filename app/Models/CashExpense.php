<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'category_id',
        'title',
        'amount',
        'expense_date',
        'proof_file',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    /**
     * The class this expense belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * The category of this expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'category_id');
    }

    /**
     * The user who created this expense.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
