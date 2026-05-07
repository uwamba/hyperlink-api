<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('session_id')->constrained('chat_sessions')->onDelete('cascade');
        $table->enum('sender', ['user', 'bot', 'agent']);
        $table->text('message');
        $table->uuid('agent_id')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};