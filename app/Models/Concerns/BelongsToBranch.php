<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    /**
     * Batasi query ke data yang boleh dilihat $user.
     * Pusat -> semua cabang; user cabang -> hanya cabangnya.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAllBranchScope()) {
            return $query;
        }

        return $query->where($this->qualifyColumn('branch_id'), (int) $user->branch_id);
    }
}
