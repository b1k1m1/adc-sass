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
        Schema::create('insurance_companies', function (Blueprint $table) {

            $table->bigIncrements('id');

            // Short code that matches claims.insurance_id (e.g. 'WELLCARE', 'UHCNJ')
            $table->string('code', 50)->unique();

            // Full display name ("WellCare of New Jersey", "United Healthcare Community Plan")
            $table->string('name', 150);

            // EDI payer ID (for clearinghouse / 837P)
            $table->string('payer_id', 20)->nullable();   // you’ll fill this later from admin

            // Optional: address info (for paper claims / reference)
            $table->string('address1', 150)->nullable();
            $table->string('address2', 150)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('phone', 25)->nullable();

            // Program type can be handy later (Medicaid / Medicare / Commercial / Other)
            $table->string('program_type', 30)->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_companies');
    }
};
