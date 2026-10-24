<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class Attendance extends Model
{
    use HasCompositeKey;

    protected $table = 'partattendence';
    protected $primaryKey =['ParticipantId','CalenderDate'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;

}
