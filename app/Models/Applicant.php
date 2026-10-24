<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicants';

    protected $fillable = [
        'form_no',
        'participant_name',
        'birthday',
        'age',
        'religion',
        'no_of_children',
        'no_of_grandchildren',
        'past_occupation',
        'no_of_years',
        'current_living_conditions',
        'hha_services',

        'physical_limitations',
        'effect_on_participation',

        'mobility',
        'motor',
        'sensory',
        'vision_problem_specify',
        'hearing_problem_specify',

        'attention_span',
        'cognitive_status',
        'cognitive_other',
        'how_often_attends_program',

        'emotional_reactions',
        'emotional_other',

        'task_technical_skills',
        'task_other',

        'interpersonal_group_skills',
        'interpersonal_other',
        'interpersonal_comments',
        'level_of_interaction',

        'interests_activities',
        'interests_other',

        'objectives',
        'benefit_from_activities',
        'activities_restrictions',
        'comments_plan',

        'staff_signature',
        'signature_date',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birthday' => 'date',
        'signature_date' => 'date',
        'mobility' => 'array',
        'motor' => 'array',
        'sensory' => 'array',
        'cognitive_status' => 'array',
        'emotional_reactions' => 'array',
        'task_technical_skills' => 'array',
        'interpersonal_group_skills' => 'array',
        'interests_activities' => 'array',
        'objectives' => 'array',
    ];
}
