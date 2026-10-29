<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_source_id')->constrained()->cascadeOnDelete();
            $table->longText('raw_message');
            $table->char('checksum', 64);
            $table->timestamp('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_logs');
    }
};