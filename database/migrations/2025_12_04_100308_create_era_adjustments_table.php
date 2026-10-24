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
        Schema::create('era_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('era_claim_id');   // FK to era_claims.id
            $table->integer('svc_sequence')->nullable();  // null = claim-level CAS, not tied to a specific SVC
            $table->enum('level', ['claim', 'line'])->default('claim');

            $table->string('group_code', 2)->nullable();   // CO / PR / PI / CR
            $table->string('reason_code', 5)->nullable();  // e.g., 45, 97
            $table->decimal('amount', 10, 2)->nullable();
            $table->integer('quantity')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('era_adjustments');
    }
};
