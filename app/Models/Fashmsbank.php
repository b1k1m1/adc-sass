<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fashmsbank extends Model
{
    protected $table = 'fashmsbank';
    protected $guarded = [];
    protected $primaryKey = 'account_id';
    public $timestamps = false;
}
