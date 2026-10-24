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
        Schema::create('claim_batches', function (Blueprint $table) {
            $table->id();
            $table->dateTime('batch_timestamp');          // when batch was generated
            $table->string('edi_filename');               // 837P .txt
            $table->string('pdf_filename')->nullable();   // summary .pdf
            $table->unsignedInteger('total_claims');      // how many claims
            $table->decimal('total_amount', 10, 2);       // sum of total_amount
            $table->unsignedBigInteger('created_by')->nullable(); // user id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_batches');
    }
};
