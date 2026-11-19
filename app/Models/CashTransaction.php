<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'class_id',
        'category_id',
        'type',
        'title',
        'amount',
        'transaction_date',
        'payment_id',
        'proof_file',
        'notes',
        'voided_at',
        'voided_by',
        'void_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
            'voided_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'category_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // ─── Scopes & Helpers ─────────────────────────────────────

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function scopeNotVoided($query)
    {
        return $query->whereNull('voided_at');
    }

    public function scopeVoided($query)
    {
        return $query->whereNotNull('voided_at');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'in')->whereNull('voided_at');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'out')->whereNull('voided_at');
    }

    public static function generateTransactionNumber(string $type = 'in'): string
    {
        $prefix = strtoupper($type) === 'IN' ? 'TRX-IN' : 'TRX-OUT';
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d-%s', $prefix, $date, $count, $random);
    }
}
