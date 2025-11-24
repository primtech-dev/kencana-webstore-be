<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanAmount extends Model
{
    protected $table = 'loan_amounts';

    protected $fillable = [
        'amount'
    ];

    public function loanInstallments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class, 'loan_amount_id', 'id');
    }
}
