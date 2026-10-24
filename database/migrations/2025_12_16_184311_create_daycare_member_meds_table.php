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
       Schema::create('daycare_member_meds', function (Blueprint $table) {
            $table->bigIncrements('id');

            // member link
            $table->string('participant_id', 4)->index();

            // medication row fields
            $table->unsignedInteger('line_no')->default(1);

            $table->string('medicine_nm', 150)->nullable();

            // physician
            $table->string('doctor_id', 4)->nullable();
            $table->string('physician_name', 100)->nullable(); // snapshot: "Last, First"

            // store final text values (dropdown OR manual)
            $table->string('dosage_nm', 50)->nullable();
            $table->string('dosage_freq', 50)->nullable();

            // nurse row comments (stopped/discontinued/dose changed etc.)
            $table->string('comments', 150)->nullable();

            // optional status helpers
            $table->string('status', 20)->default('active');   // active/stopped/discontinued
            $table->date('status_date')->nullable();           // when stopped/discontinued

            $table->timestamps();

            $table->index(['participant_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daycare_member_meds');
    }
};
