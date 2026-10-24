<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantActivityAssessment extends Model
{
    protected $table = 'participant_activity_assessments';

    protected $fillable = [
        'ParticipantId',
        'assessment_date',
        'shift',
        'assessment_type',
        'diagnosis',
        'date_of_birth',
        'responsible_party',
        'phone',
        'activity_preferences',
        'activities',
        'admission_date',
        'functional_needs',
        'may_participate_activities',
        'may_have_alcoholic_beverages',
        'may_leave_facility_trips',
        'may_participate_volunteer_work',
        'doctor_orders_other',
        'summary',
        'completed_by',
        'completed_title',
        'completed_date',
        'user_cd',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'completed_date' => 'date',
        'activities' => 'array',
        'functional_needs' => 'array',
        'may_participate_activities' => 'boolean',
        'may_have_alcoholic_beverages' => 'boolean',
        'may_leave_facility_trips' => 'boolean',
        'may_participate_volunteer_work' => 'boolean',
    ];
}
