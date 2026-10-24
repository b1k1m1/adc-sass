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
        Schema::create('daycare_med_assessments', function (Blueprint $table) {
           $table->bigIncrements('id');

            $table->string('participant_id', 4)->index();

            $table->string('medical_record_no', 50)->nullable();

            $table->date('assessment_date')->nullable();
            $table->string('nurse_name', 80)->nullable();

            // From your pdf: splints/braces, home glucose monitoring frequency, physical therapy
            $table->string('splints_braces', 50)->nullable();          // Yes/No or free text
            $table->string('home_glucose_monitoring_freq', 50)->nullable();
            $table->string('physical_therapy', 50)->nullable();

            // OTC meds note (Tylenol/Advil/etc.)
            $table->string('otc_meds', 200)->nullable();

            // Reminder method checkboxes
            $table->boolean('rem_take_independently')->default(false);
            $table->boolean('rem_caregiver_gives')->default(false);
            $table->boolean('rem_follows_label')->default(false);
            $table->boolean('rem_pill_minder')->default(false);
            $table->boolean('rem_schedule_calendar')->default(false);
            $table->string('rem_other', 80)->nullable();

            // Pharmacy provider + phone
            $table->string('pharmacy_provider', 80)->nullable();
            $table->string('pharmacy_phone', 30)->nullable();

            // Yes/No questions
            $table->string('takes_as_ordered', 10)->nullable();        // Yes/No
            $table->string('takes_while_center', 10)->nullable();      // Yes/No

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['participant_id', 'assessment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daycare_med_assessments');
    }
};
