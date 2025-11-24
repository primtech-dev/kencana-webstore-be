<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenor extends Model
{
    protected $table = 'tenors';

    protected $fillable = [
        'months'
    ];

    public function loanInstallments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class, 'tenor_id', 'id');
    }
}
