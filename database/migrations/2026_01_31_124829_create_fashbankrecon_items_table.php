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
        Schema::create('fashbankrecon_items', function (Blueprint $table) {

                    // link to reconciliation header
            $table->unsignedBigInteger('recon_id');

            // reference to fashtrans composite key
            $table->unsignedBigInteger('trans_no');
            $table->dateTime('trans_dt');
            $table->string('serial_no', 4);

            // cleared info
            $table->string('cleared_flag', 1)->default('Y'); // always Y when present
            $table->dateTime('cleared_dt')->nullable();

            // composite primary key
            $table->primary(
                ['recon_id', 'trans_no', 'trans_dt', 'serial_no'],
                'pk_fashbankrecon_items'
            );

            // indexes for faster joins back to fashtrans
            $table->index(
                ['trans_no', 'trans_dt', 'serial_no'],
                'idx_fashbankrecon_items_txn'
            );

            $table->index('recon_id', 'idx_fashbankrecon_items_recon');

            // (optional) you can add FK if you want, but you said no FK style
            // so we skip foreign key constraints to stay consistent with your schema
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fashbankrecon_items');
    }
};
