<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
