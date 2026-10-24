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
        Schema::create('fashbankrecon', function (Blueprint $table) {
            // primary key
            $table->bigIncrements('recon_id');

            // company + bank + period (unique per month)
            $table->string('company_id', 4);
            $table->string('bank_id', 18);              // fashmsbank.account_id
            $table->integer('period_year');             // e.g. 2026
            $table->integer('period_month');            // 1..12

            // statement balances and dates
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->dateTime('opening_dt');

            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->dateTime('closing_dt');

            // service charge (acts like payment)
            $table->dateTime('service_charge_dt')->nullable();
            $table->decimal('service_charge_amt', 12, 2)->default(0);
            $table->string('service_charge_account_id', 18)->nullable();

            // interest earned (acts like deposit)
            $table->dateTime('interest_dt')->nullable();
            $table->decimal('interest_amt', 12, 2)->default(0);
            $table->string('interest_account_id', 18)->nullable();

            // status OPEN / CLOSED
            $table->string('status', 10)->default('OPEN');

            // common audit fields (same pattern as your other tables)
            $table->string('user_cd', 4)->nullable();
            $table->dateTime('update_dt')->nullable();
            $table->string('update_flag', 1)->nullable();
            $table->string('trans_flag', 1)->nullable();

            // one reconciliation per bank per month per company
            $table->unique(
                ['company_id', 'bank_id', 'period_year', 'period_month'],
                'uq_fashbankrecon_bank_period'
            );

            // helpful indexes
            $table->index(['company_id', 'bank_id'], 'idx_fashbankrecon_bank');
            $table->index(['company_id', 'status'], 'idx_fashbankrecon_status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fashbankrecon');
    }
};
