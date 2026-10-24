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
        Schema::create('era_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name');
            $table->date('payment_date')->nullable();     // from BPR
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payer_name')->nullable();     // from N1*PR
            $table->string('trace_number')->nullable();   // from TRN
            $table->text('raw_content');                  // full X12 text
            $table->timestamps();

        });

        Schema::create('era_claims', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('era_file_id');
            $table->string('patient_control_number')->index(); // CLP01 (your claim_no)
            $table->string('payer_claim_number')->nullable();  // CLP07
            $table->string('claim_status_code', 2)->nullable(); // CLP02
            $table->decimal('total_charge', 10, 2)->nullable(); // CLP03
            $table->decimal('total_paid', 10, 2)->nullable();   // CLP04
            $table->decimal('patient_responsibility', 10, 2)->nullable(); // CLP05
            $table->string('member_id')->nullable();            // from NM1*QC/IL if we parse later
            $table->timestamps();

            $table->foreign('era_file_id')->references('id')->on('era_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('era_claims');
        Schema::dropIfExists('era_files');
    }
};
