<?php

namespace App\Transfer;

class LoanResult {
    public ?float $payment = null;
    public ?float $loan = null;
    public ?float $interest = null;
    public ?float $years = null;
    public string $currency = 'USD';
}
