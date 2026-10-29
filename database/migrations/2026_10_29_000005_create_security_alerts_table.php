<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('detection_rules')->cascadeOnDelete();
            $table->string('source_ip', 45);
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info']);
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open');
            $table->timestamp('triggered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};