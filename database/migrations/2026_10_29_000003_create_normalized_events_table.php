<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normalized_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_log_id')->constrained()->cascadeOnDelete();
            $table->timestamp('timestamp')->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->string('destination_ip', 45)->nullable();
            $table->string('username')->nullable();
            $table->string('event_category');
            $table->string('event_action');
            $table->string('event_outcome');
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info']);
            $table->json('parsed_data')->nullable();
            $table->timestamps();

            $table->index('timestamp');
            $table->index('source_ip');
            $table->index('severity');
            $table->index('event_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normalized_events');
    }
};