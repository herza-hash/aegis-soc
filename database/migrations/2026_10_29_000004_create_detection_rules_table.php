<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('condition_type');
            $table->unsignedInteger('threshold');
            $table->unsignedInteger('time_window_minutes');
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_rules');
    }
};