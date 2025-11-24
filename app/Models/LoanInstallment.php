<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    protected $table = 'loan_installments';

    protected $fillable = [
        'loan_amount_id', 'tenor_id', 'installment'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function loanAmount(): BelongsTo
    {
        return $this->belongsTo(LoanAmount::class, 'loan_amount_id', 'id');
    }

    public function tenor(): BelongsTo
    {
        return $this->belongsTo(Tenor::class, 'tenor_id', 'id');
    }
}
