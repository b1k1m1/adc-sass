<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            /*
             * A/R fields
             * These are used after claim is submitted and ERA is processed.
             */
            $table->date('submitted_date')->nullable()->after('status');
            $table->date('paid_date')->nullable()->after('submitted_date');

            $table->decimal('adjustment_amount', 10, 2)->default(0.00)->after('ins1_paid');
            $table->decimal('patient_responsibility', 10, 2)->default(0.00)->after('adjustment_amount');
            $table->decimal('deduction_amount', 10, 2)->default(0.00)->after('patient_responsibility');
            $table->decimal('writeoff_amount', 10, 2)->default(0.00)->after('deduction_amount');
            $table->decimal('balance_amount', 10, 2)->default(0.00)->after('writeoff_amount');

            /*
             * ERA / payer reference
             */
            $table->string('payer_claim_number', 100)->nullable()->after('payer_id');
            $table->timestamp('era_posted_at')->nullable()->after('paid_date');
        });

        /*
         * Add indexes safely.
         * We use raw SQL with checks because some indexes may already exist.
         */
        $this->addIndexIfNotExists('claims', 'idx_claims_claim_no', 'claim_no');
        $this->addIndexIfNotExists('claims', 'idx_claims_participant_id', 'ParticipantId');
        $this->addIndexIfNotExists('claims', 'idx_claims_service_from', 'service_from');
        $this->addIndexIfNotExists('claims', 'idx_claims_status', 'status');
        $this->addIndexIfNotExists('claims', 'idx_claims_balance_amount', 'balance_amount');

        /*
         * Backfill existing claims.
         * For sent/accepted/paid/denied claims, create a starting balance.
         */
        DB::statement("
            UPDATE claims
            SET balance_amount = GREATEST(total_amount - IFNULL(ins1_paid, 0), 0)
            WHERE balance_amount = 0
        ");

        /*
         * For already-paid claims, force balance to zero.
         */
        DB::statement("
            UPDATE claims
            SET balance_amount = 0
            WHERE status = 'paid'
        ");
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_date',
                'paid_date',
                'adjustment_amount',
                'patient_responsibility',
                'deduction_amount',
                'writeoff_amount',
                'balance_amount',
                'payer_claim_number',
                'era_posted_at',
            ]);
        });
    }

    private function addIndexIfNotExists(string $tableName, string $indexName, string $columnName): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();

        if (!$exists) {
            DB::statement("ALTER TABLE {$tableName} ADD INDEX {$indexName} ({$columnName})");
        }
    }
};
