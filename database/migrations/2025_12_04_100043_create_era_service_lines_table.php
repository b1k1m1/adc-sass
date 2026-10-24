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
        Schema::create('era_service_lines', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->unsignedBigInteger('era_claim_id');   // FK to era_claims.id
            $table->integer('svc_sequence')->nullable();  // 1,2,3... per claim

            $table->string('procedure_code', 50)->nullable();   // from SVC01 or SVC02
            $table->string('modifier1', 10)->nullable();
            $table->string('modifier2', 10)->nullable();
            $table->string('modifier3', 10)->nullable();
            $table->string('modifier4', 10)->nullable();

            $table->date('service_from')->nullable();
            $table->date('service_to')->nullable();

            $table->decimal('charge_amount', 10, 2)->nullable(); // SVC02
            $table->decimal('paid_amount', 10, 2)->nullable();   // SVC03 or SVC04 depending on payer
            $table->decimal('patient_responsibility', 10, 2)->nullable(); // optional if needed

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('era_service_lines');
    }
};
