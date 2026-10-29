<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address', 45);
            $table->enum('type', ['syslog', 'file', 'api']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sources');
    }
};