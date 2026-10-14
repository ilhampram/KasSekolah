<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'cash_bill_id',
        'student_id',
        'amount_paid',
        'payment_date',
        'status',
        'payment_method',
        'proof_file',
        'notes',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid'  => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    /**
     * The cash bill this payment is for.
     */
    public function cashBill(): BelongsTo
    {
        return $this->belongsTo(CashBill::class, 'cash_bill_id');
    }

    /**
     * The student who made this payment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * The user who verified this payment.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Associated cash transaction.
     */
    public function cashTransaction(): HasOne
    {
        return $this->hasOne(CashTransaction::class, 'payment_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public static function generateReceiptNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('KWT-%s-%04d-%s', $date, $count, $random);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
