<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_transactions', function (Blueprint $table) {
            $table->id();

            /*
             * Links to claim and ERA records
             */
            $table->unsignedBigInteger('claim_id')->nullable();
            $table->unsignedBigInteger('era_file_id')->nullable();
            $table->unsignedBigInteger('era_claim_id')->nullable();

            /*
             * Snapshot/reference fields for easy reporting without joins
             */
            $table->string('claim_no', 20)->nullable();
            $table->string('ParticipantId', 10)->nullable();
            $table->string('payer_claim_number', 100)->nullable();

            /*
             * Transaction information
             */
            $table->date('transaction_date')->nullable();
            $table->string('transaction_type', 50);
            $table->decimal('amount', 10, 2)->default(0.00);

            /*
             * ERA adjustment / denial / remark codes
             */
            $table->string('group_code', 10)->nullable();
            $table->string('reason_code', 20)->nullable();
            $table->string('remark_code', 50)->nullable();
            $table->string('description', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            /*
             * Indexes
             */
            $table->index('claim_id', 'idx_claim_tx_claim_id');
            $table->index('era_file_id', 'idx_claim_tx_era_file_id');
            $table->index('era_claim_id', 'idx_claim_tx_era_claim_id');
            $table->index('claim_no', 'idx_claim_tx_claim_no');
            $table->index('ParticipantId', 'idx_claim_tx_participant_id');
            $table->index('transaction_type', 'idx_claim_tx_type');
            $table->index('transaction_date', 'idx_claim_tx_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_transactions');
    }
};
