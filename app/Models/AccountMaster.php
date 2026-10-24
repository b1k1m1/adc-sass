<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMaster extends Model
{
    protected $table = 'accountmasters';
    protected $guarded = [];
    protected $primaryKey = 'party_cd';
    public $timestamps = false;
}
