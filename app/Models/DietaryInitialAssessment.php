<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietaryInitialAssessment extends Model
{
     protected $table = 'dietary_initial_assessments';

    protected $guarded = [];

    protected $casts = [
    'assessment_date' => 'date',
    // ...
    ];
}
