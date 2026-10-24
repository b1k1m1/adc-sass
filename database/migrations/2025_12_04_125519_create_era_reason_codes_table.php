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
        Schema::create('era_reason_codes', function (Blueprint $table) {
        $table->id();
        $table->string('group_code', 4);       // PR, CO, OA, PI, etc.
        $table->string('reason_code', 10);     // 1, 2, 3, 27, 45, 97, etc.
        $table->string('short_desc');          // short label
        $table->text('long_desc')->nullable(); // optional detailed desc
        $table->boolean('active')->default(true);
        $table->timestamps();

        $table->unique(['group_code', 'reason_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('era_reason_codes');
    }
};
