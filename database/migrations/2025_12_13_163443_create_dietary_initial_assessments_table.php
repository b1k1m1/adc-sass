<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dietary_initial_assessments', function (Blueprint $table) {
            $table->id();

            // Common pattern
            $table->string('participant_id', 4);
            $table->string('medical_record_no', 50)->nullable(); // manual entry (shown next to DOB on screen)

            // Dates
            $table->date('assessment_date')->nullable();

            // Manual fields
            $table->string('height', 50)->nullable();
            $table->string('weight', 50)->nullable();
            $table->string('ibw', 50)->nullable();
            $table->string('weight_changes', 255)->nullable();

            $table->string('eating_problems_devices', 255)->nullable();
            $table->string('appetite', 100)->nullable();
            $table->string('religious_restrictions', 255)->nullable();
            $table->string('teeth', 100)->nullable();
            $table->string('dentures', 100)->nullable();

            $table->string('diet_order', 255)->nullable();
            $table->string('food_allergy', 255)->nullable();
            $table->string('diagnosis', 255)->nullable();
            $table->string('medications', 255)->nullable();

            $table->text('labs')->nullable();
            $table->text('food_preferences_dislikes')->nullable();
            $table->text('initial_assessment')->nullable();

            // Goals (checkboxes)
            $table->boolean('goal_tolerate_current_diet')->default(false);
            $table->boolean('goal_bp_bg_within_limits')->default(false);
            $table->boolean('goal_weight_stable_or_lose_gain')->default(false);
            $table->boolean('goal_select_healthy_choices')->default(false);
            $table->boolean('goal_verbalize_knowledge_follow_home')->default(false);

            // Interventions (checkboxes)
            $table->boolean('int_educate_decrease_salty_alternates')->default(false);
            $table->boolean('int_encourage_change_1_2_per_week_month')->default(false);
            $table->boolean('int_encourage_consume_over_75_each_meal')->default(false);
            $table->boolean('int_encourage_increase_dietary_fiber')->default(false);
            $table->boolean('int_educate_avoid_dark_green_vit_k')->default(false);
            $table->boolean('int_educate_cho_counting_timing_exercise')->default(false);
            $table->boolean('int_educate_increase_decrease_fluids')->default(false);
            $table->boolean('int_monitor_alt_food_consistency')->default(false);
            $table->boolean('int_encourage_increase_protein')->default(false);
            $table->boolean('int_monitor_monthly_weights')->default(false);
            $table->boolean('int_encourage_physical_activity')->default(false);

            // Signature
            $table->string('dietitian_signature_title', 150)->nullable();
            $table->date('signed_date')->nullable();

            $table->timestamps();

            $table->index(['participant_id', 'assessment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dietary_initial_assessments');
    }
};

