<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'category_id',
        'title',
        'amount',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount'   => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    /**
     * The class this bill belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * The category of this bill.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'category_id');
    }

    /**
     * Payments made against this bill.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'cash_bill_id');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'cash_bill_id')->where('status', 'verified');
    }

    public function totalCollected(): float
    {
        if ($this->relationLoaded('verifiedPayments')) {
            return (float) $this->verifiedPayments->sum('amount_paid');
        }

        return (float) $this->verifiedPayments()->sum('amount_paid');
    }

    public function studentPaymentStatus(int $studentId): array
    {
        if ($this->relationLoaded('payments')) {
            $paid = (float) $this->payments
                ->where('student_id', $studentId)
                ->where('status', 'verified')
                ->sum('amount_paid');
        } else {
            $paid = (float) $this->payments()
                ->where('student_id', $studentId)
                ->where('status', 'verified')
                ->sum('amount_paid');
        }

        $billAmount = (float) $this->amount;
        $remaining = max(0, $billAmount - $paid);

        $status = 'belum_bayar';
        if ($paid >= $billAmount) {
            $status = 'lunas';
        } elseif ($paid > 0) {
            $status = 'cicilan';
        }

        return [
            'paid'      => $paid,
            'remaining' => $remaining,
            'status'    => $status,
        ];
    }
}
