<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class Partbp extends Model
{
    use HasCompositeKey;

    protected $table = 'partbp';
    protected $primaryKey =['ParticipantId','CalenderDate'];
    public $incrementing = false;
    protected $guarded = [];
    public $timestamps = false;

}
