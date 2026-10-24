<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_notice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('notice_date');
            $table->timestamp('shown_at')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'notice_date'], 'uniq_user_notice_date');
            $table->index('user_id');
            $table->index('notice_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_notice_logs');
    }
};
