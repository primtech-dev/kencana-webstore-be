<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockImportLog extends Model
{
    public $timestamps = false;

    protected $table = 'stock_import_logs';

    protected $fillable = [
        'batch_id', 'user_id', 'source', 'file_name',
        'rows_total', 'rows_changed', 'rows_zeroed', 'rows_skipped', 'branches', 'created_at',
    ];

    protected $casts = [
        'branches' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
