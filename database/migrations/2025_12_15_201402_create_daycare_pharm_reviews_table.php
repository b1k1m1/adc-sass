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
        Schema::create('daycare_pharm_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Member link (members.ParticipantId)
            $table->unsignedBigInteger('participant_id')->index();

            // Your standard manual field
            $table->string('medical_record_no', 50)->nullable();

            // Review date entered by pharmacist
            $table->date('review_date')->index();

            // Flexible period: Monthly (M) / Quarterly (Q)
            $table->char('period_type', 1)->default('Q')->index(); // M or Q

            // Monthly: 2025-12, Quarterly: 2025-Q4
            $table->string('period_key', 10)->index();

            // Tab 1: Comments
            $table->longText('comments')->nullable();

            // Optional workflow
            $table->string('status', 20)->default('draft')->index(); // draft/completed
            $table->dateTime('completed_at')->nullable();

            // Audit (optional)
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            $table->timestamps();

            // Helpful composite index for fast lookups per member/period
            $table->index(['participant_id', 'period_type', 'period_key'], 'idx_pharm_member_period');
        });

    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daycare_pharm_reviews');
    }
};
