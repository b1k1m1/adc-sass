<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NursingAssessment extends Model
{
    protected $table = 'nursing_assessments';

    protected $guarded = [];

    protected $casts = [
    'assessment_date' => 'date',
    // ...
    ];

}
