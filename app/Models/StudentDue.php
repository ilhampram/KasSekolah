<?php

namespace App\Models;

/**
 * Alias of CashBill for compatibility with StudentDue naming.
 */
class StudentDue extends CashBill
{
    protected $table = 'cash_bills';
}
