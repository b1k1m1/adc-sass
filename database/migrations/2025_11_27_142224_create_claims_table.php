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
        Schema::create('claims', function (Blueprint $table) {
            $table->bigIncrements('id'); // internal PK

            // Business identifiers
            $table->string('claim_no', 6);          // Medisoft-style claim number
            $table->unsignedTinyInteger('line_no')  // 1,2,3... per claim
                ->default(1);

            // Member
            $table->string('ParticipantId', 20);

            // Service dates
            $table->date('service_from');
            $table->date('service_to');

            // Codes & units
            $table->string('proc_code', 6)->default('S5102'); // Procedure varchar(6)
            $table->string('m1', 3)->default('U1');           // Modifier 1 varchar(3)
            $table->unsignedTinyInteger('units');             // 0–7 (manual)
            $table->decimal('amount_per_unit', 5, 2);         // 94.66 etc.
            $table->decimal('total_amount', 7, 2);            // Units * Amount

            // Diagnosis
            $table->string('diag1', 6)->nullable();
            $table->string('diag2', 6)->nullable();

            // Provider / POS
            $table->string('provider', 3)->default('DM');     // later from company_info
            $table->string('pos', 2)->default('99');          // POS 99

            // Financials
            $table->decimal('allowed', 7, 2)->nullable();     // usually = total_amount
            $table->decimal('ins1_paid', 7, 2)->default(0);   // Insurance 1 amount paid

            // Resubmission / status tracking
            $table->string('original_claim_no', 6)->nullable();  // link to first claim (if resubmitted)
            $table->enum('status', ['draft','submitted','rejected','paid','void'])
                ->default('draft');

            $table->timestamps();

            // Useful indexes
            $table->unique(['claim_no', 'line_no']); // one line number per claim
            $table->index(['ParticipantId', 'service_from', 'service_to']);
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
