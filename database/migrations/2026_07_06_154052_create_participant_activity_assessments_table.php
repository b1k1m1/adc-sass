<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_activity_assessments', function (Blueprint $table) {
            $table->id();

            $table->string('ParticipantId', 10);
            $table->date('assessment_date');
            $table->string('shift', 1)->default('A'); // A = Morning, N = Noon

            // Client information
            $table->string('assessment_type', 50)->nullable();
            // initial_admission, annual, update

            $table->text('diagnosis')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('responsible_party', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('activity_preferences')->nullable();

            // Activity preference grid stored as JSON
            $table->json('activities')->nullable();

            // Page 2
            $table->date('admission_date')->nullable();

            $table->json('functional_needs')->nullable();
            /*
                vision, hearing, communication, mobility, fine_motor,
                orientation, psychosocial, dietary_allergy,
                precautions, other_needs
            */

            $table->boolean('may_participate_activities')->default(false);
            $table->boolean('may_have_alcoholic_beverages')->default(false);
            $table->boolean('may_leave_facility_trips')->default(false);
            $table->boolean('may_participate_volunteer_work')->default(false);
            $table->string('doctor_orders_other', 255)->nullable();

            $table->text('summary')->nullable();

            $table->string('completed_by', 150)->nullable();
            $table->string('completed_title', 150)->nullable();
            $table->date('completed_date')->nullable();

            $table->string('user_cd', 50)->nullable();

            $table->timestamps();

            $table->unique(
                ['ParticipantId', 'assessment_date', 'shift'],
                'activity_assessment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_activity_assessments');
    }
};
