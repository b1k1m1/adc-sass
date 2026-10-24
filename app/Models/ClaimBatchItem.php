<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimBatchItem extends Model
{
    protected $fillable = [
        'claim_batch_id',
        'claim_id',
    ];
}
