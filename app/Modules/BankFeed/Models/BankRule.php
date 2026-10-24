<?php

namespace App\Modules\BankFeed\Models;

use Illuminate\Database\Eloquent\Model;

class BankRule extends Model
{
    protected $table = 'bank_rules';

    protected $fillable = [
        'bank_id',
        'match_type',
        'pattern',
        'bank_category',
        'party_cd',
        'account_type',
        'is_active',
        'priority',
    ];
}
