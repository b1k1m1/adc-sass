<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class ParticipantActivityAssesment extends Model
{
    protected $table = 'participant_activity_assesment'; // note: table name
    protected $primaryKey =['participantid','assessedyear', 'quarter'];
    public $timestamps = false;         // we use update_dt
    public $incrementing = false;       // composite PK, no auto-increment
    protected $keyType = 'string';

    // allow mass-assign
    protected $guarded = [];
    protected $casts = ['assessmentdate' => 'datetime',
                        'update_dt'      => 'datetime',
                       ];

}
