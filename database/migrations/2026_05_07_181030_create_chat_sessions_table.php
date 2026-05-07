<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('location')->nullable();
            $table->uuid('client_id')->nullable(); // linked if existing client
            $table->enum('status', ['active', 'waiting_agent', 'with_agent', 'closed'])->default('active');
            $table->string('issue_category')->nullable(); // selected quick issue
            $table->boolean('is_verified_client')->default(false);
            $table->timestamp('agent_joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};