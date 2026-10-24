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
        Schema::create('claim_batch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_batch_id');
            $table->unsignedBigInteger('claim_id');
            $table->timestamps();

            $table->foreign('claim_batch_id')
                ->references('id')->on('claim_batches')
                ->onDelete('cascade');

            // optional if you want FK
            // $table->foreign('claim_id')
            //     ->references('id')->on('claims')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_batch_items');
    }
};
