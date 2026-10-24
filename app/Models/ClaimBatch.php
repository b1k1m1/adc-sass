<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimBatch extends Model
{
    protected $fillable = [
        'batch_timestamp',
        'edi_filename',
        'pdf_filename',
        'total_claims',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'batch_timestamp' => 'datetime',
    ];

    public function items()
    {
    return $this->hasMany(\App\Models\ClaimBatchItem::class);
    }
}
