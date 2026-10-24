<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionQuarterlyAssessment extends Model
{
    protected $table = 'nutrition_quarterly_assessments';

    protected $guarded = [];

    protected $casts = [
    'assessment_date' => 'date',
    // ...
    ];
}
