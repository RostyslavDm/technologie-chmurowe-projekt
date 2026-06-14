<?php

namespace App\Forms;

class LoanForm {
    public ?string $loan     = null;
    public ?string $interest = null;
    public ?string $years    = null;
    public string  $currency = 'USD';
}
