<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class Partadl extends Model
{
    use HasCompositeKey;

    protected $table = 'partadl';
    protected $primaryKey =['participantid','calenderDate'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;
}


