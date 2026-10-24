<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class Fagltrjournal extends Model
{
   protected $table = 'fagltrjournal';
    protected $primaryKey =['trans_no','serial_no', 'trans_dt'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
}
