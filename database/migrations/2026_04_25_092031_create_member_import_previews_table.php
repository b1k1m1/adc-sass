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
        Schema::create('member_import_previews', function (Blueprint $table) {
            $table->id();
        $table->uuid('batch_id');
        $table->integer('row_no')->nullable();

        $table->string('ParticipantId', 4)->nullable();
        $table->string('LastName', 15)->nullable();
        $table->string('FirstName', 15)->nullable();
        $table->string('MiddleName', 1)->nullable();
        $table->string('Address1', 25)->nullable();
        $table->string('Address2', 25)->nullable();
        $table->string('City', 15)->nullable();
        $table->string('State', 2)->nullable();
        $table->string('ZipCode', 10)->nullable();
        $table->string('Shift', 10)->nullable();
        $table->string('Phone1', 10)->nullable();
        $table->string('Trans_flag', 1)->nullable();
        $table->string('HMO_Enrolled', 45)->nullable();
        $table->string('medicaid_HMO_id', 20)->nullable();
        $table->string('medicaid_PA_no', 20)->nullable();
        $table->dateTime('medicaid_PA_Start')->nullable();
        $table->dateTime('medicaid_PA_End')->nullable();
        $table->string('PrimaryDiagnosis1', 15)->nullable();
        $table->string('SecondaryDiag1', 15)->nullable();
        $table->dateTime('DateOfBirth')->nullable();
        $table->string('SSNo', 11)->nullable();
        $table->string('Sex', 1)->nullable();

        $table->string('import_status')->default('READY');
        $table->text('error_message')->nullable();

        $table->timestamps();

        $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_import_previews');
    }
};
