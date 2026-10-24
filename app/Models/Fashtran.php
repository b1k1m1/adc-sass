<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class Fashtran extends Model
{
    use HasCompositeKey;

    protected $table = 'fashtrans';
    protected $primaryKey =['trans_no','trans_dt', 'serial_no'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
}
