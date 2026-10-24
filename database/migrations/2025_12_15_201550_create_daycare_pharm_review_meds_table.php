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
       Schema::create('daycare_pharm_review_meds', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Link to header
            $table->unsignedBigInteger('review_id')->index();

            // Grid order
            $table->unsignedInteger('line_no')->default(1);

            // Legacy columns
            $table->string('medicine_nm', 150)->nullable();
            $table->string('dosage_nm', 50)->nullable();
            $table->string('dosage_freq', 50)->nullable();
            $table->string('doctor_id', 4)->nullable();
            $table->string('physician_name', 150)->nullable();


            $table->timestamps();

            $table->index(['review_id', 'line_no'], 'idx_pharm_review_line');

            $table->index('doctor_id', 'idx_pharm_meds_doctor');

            // Optional FK (safe). If you prefer NO FKs, tell me and I’ll remove it.
            $table->foreign('review_id')
                ->references('id')->on('daycare_pharm_reviews')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daycare_pharm_review_meds');
    }
};
