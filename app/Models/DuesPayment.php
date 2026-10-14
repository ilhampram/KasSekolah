<?php

namespace App\Models;

/**
 * Alias of Payment for compatibility with DuesPayment naming.
 */
class DuesPayment extends Payment
{
    protected $table = 'payments';
}
