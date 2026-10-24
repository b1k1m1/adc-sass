<?php

namespace App\Models;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';
    protected $guarded = [];
    protected $primaryKey = 'ParticipantId';
    public $timestamps = false;

}
