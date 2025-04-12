<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supports', function (Blueprint $table) {
            $table->uuid('id')->primary();;
            $table->uuid('client_id');
            $table->string('email');
            $table->text('description');
            $table->string('address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};