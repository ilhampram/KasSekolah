<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'class_name',
        'academic_year_id',
        'academic_year',
        'homeroom_teacher',
    ];

    // ─── Relationships ────────────────────────────────────────

    /**
     * Academic year this class belongs to.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Students & staff assigned to this class.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'class_id');
    }

    /**
     * Students in this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'siswa');
    }

    /**
     * Cash bills for this class.
     */
    public function cashBills(): HasMany
    {
        return $this->hasMany(CashBill::class, 'class_id');
    }

    /**
     * Cash expenses for this class.
     */
    public function cashExpenses(): HasMany
    {
        return $this->hasMany(CashExpense::class, 'class_id');
    }

    /**
     * Payments for this class (through cash bills).
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, CashBill::class, 'class_id', 'cash_bill_id');
    }
}
