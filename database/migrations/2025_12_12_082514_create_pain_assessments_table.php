<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pain_assessments', function (Blueprint $table) {
            $table->id();

            $table->string('member_id', 4);     // ParticipantId
            $table->char('shift_type', 1);       // A / N
            $table->string('mr_no', 50)->nullable();

            $table->date('assessment_date')->nullable();

            // Reason for assessment (checkboxes) :contentReference[oaicite:1]{index=1}
            $table->boolean('reason_new_admission')->default(false);
            $table->boolean('reason_readmission')->default(false);
            $table->boolean('reason_further_assessment')->default(false);
            $table->boolean('reason_change_in_condition')->default(false);
            $table->boolean('reason_quarterly')->default(false);

            // 1) Location of pain (diagram + text) :contentReference[oaicite:2]{index=2}
            $table->text('pain_location')->nullable();

            // 2) Severity (faces/0-10)
            $table->unsignedTinyInteger('pain_severity_score')->nullable(); // 0-10
            $table->string('pain_severity_label', 30)->nullable();          // mild, distressing, etc.

            // Page-1 Questions + Comments table :contentReference[oaicite:3]{index=3}
            $table->text('q_present_level')->nullable();
            $table->unsignedTinyInteger('q_least_rate')->nullable();
            $table->text('q_better')->nullable();
            $table->unsignedTinyInteger('q_worst_rate')->nullable();
            $table->text('q_worse')->nullable();
            $table->string('q_continuous', 20)->nullable(); // continuous/intermittent
            $table->text('q_start')->nullable();
            $table->text('q_cause')->nullable();
            $table->unsignedTinyInteger('q_satisfied_level')->nullable();

            // 3) Quality (checkbox words) :contentReference[oaicite:4]{index=4}
            $table->boolean('qual_aching')->default(false);
            $table->boolean('qual_throbbing')->default(false);
            $table->boolean('qual_shooting')->default(false);
            $table->boolean('qual_stabbing')->default(false);
            $table->boolean('qual_penetrating')->default(false);
            $table->boolean('qual_sharp')->default(false);
            $table->boolean('qual_burning')->default(false);
            $table->boolean('qual_tender')->default(false);
            $table->boolean('qual_exhausting')->default(false);
            $table->boolean('qual_tiring')->default(false);
            $table->boolean('qual_unbearable')->default(false);
            $table->boolean('qual_numb')->default(false);
            $table->boolean('qual_nagging')->default(false);
            $table->boolean('qual_hammering')->default(false);
            $table->boolean('qual_pins_needles')->default(false);
            $table->boolean('qual_gnawing')->default(false);
            $table->boolean('qual_tingling')->default(false);
            $table->boolean('qual_stretching')->default(false);
            $table->boolean('qual_pulling')->default(false);
            $table->string('qual_other', 120)->nullable();

            // 4) Effects on activities of daily living (Yes/No + comments) :contentReference[oaicite:5]{index=5}
            $table->string('adl_sleep_yn', 5)->nullable();
            $table->string('adl_social_yn', 5)->nullable();
            $table->string('adl_appetite_yn', 5)->nullable();
            $table->string('adl_physical_yn', 5)->nullable();
            $table->string('adl_emotions_yn', 5)->nullable();
            $table->string('adl_sexuality_yn', 5)->nullable();
            $table->text('adl_comments')->nullable();

            // 5) Effects of pain on quality of life (free text)
            $table->text('quality_of_life_text')->nullable();

            // 6) Symptoms (checkboxes)
            $table->boolean('sym_constipation')->default(false);
            $table->boolean('sym_nausea')->default(false);
            $table->boolean('sym_vomiting')->default(false);
            $table->boolean('sym_fatigue')->default(false);
            $table->boolean('sym_insomnia')->default(false);
            $table->boolean('sym_depression')->default(false);
            $table->boolean('sym_drowsy')->default(false);
            $table->boolean('sym_sore_mouth')->default(false);
            $table->boolean('sym_weakness')->default(false);
            $table->boolean('sym_short_breath')->default(false);
            $table->string('sym_other', 120)->nullable();

            // 7) Behaviors (checkboxes)
            $table->boolean('beh_calling_out')->default(false);
            $table->boolean('beh_restlessness')->default(false);
            $table->boolean('beh_disorientation')->default(false);
            $table->boolean('beh_not_eating')->default(false);
            $table->boolean('beh_pacing')->default(false);
            $table->boolean('beh_not_sleeping')->default(false);
            $table->boolean('beh_withdrawn')->default(false);
            $table->boolean('beh_groaning')->default(false);
            $table->boolean('beh_rocking')->default(false);
            $table->boolean('beh_new_immobility')->default(false);
            $table->boolean('beh_tense')->default(false);
            $table->boolean('beh_distressed')->default(false);
            $table->boolean('beh_distracted')->default(false);
            $table->boolean('beh_crying')->default(false);
            $table->boolean('beh_inexpressive')->default(false);
            $table->boolean('beh_fists_clenched')->default(false);
            $table->boolean('beh_striking_out')->default(false);
            $table->boolean('beh_knees_pulled_up')->default(false);
            $table->boolean('beh_frowning')->default(false);
            $table->boolean('beh_facial_grimacing')->default(false);
            $table->boolean('beh_sad')->default(false);
            $table->boolean('beh_frighten')->default(false);
            $table->boolean('beh_resistant_movement')->default(false);
            $table->boolean('beh_pulling_pushing_away')->default(false);
            $table->string('beh_other', 120)->nullable();

            // 8) Past pain management
            $table->text('past_pain_experienced')->nullable();
            $table->text('past_pain_management')->nullable();

            // 9) Support system
            $table->text('support_system')->nullable();

            // 10) Other concerns
            $table->text('other_concerns')->nullable();

            // 11) Nursing pain diagnosis (checkbox) :contentReference[oaicite:6]{index=6}
            $table->boolean('dx_visceral')->default(false);
            $table->boolean('dx_somatic')->default(false);
            $table->boolean('dx_raised_intracranial')->default(false);
            $table->boolean('dx_naturopathic')->default(false);
            $table->boolean('dx_mixed')->default(false);
            $table->boolean('dx_unknown')->default(false);

            // Footer
            $table->date('care_plan_updated_date')->nullable();
            $table->boolean('pt_evaluation_yes')->default(false);
            $table->text('plan_of_care')->nullable();
            $table->string('signature', 120)->nullable();

            // Audit
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pain_assessments');
    }
};
