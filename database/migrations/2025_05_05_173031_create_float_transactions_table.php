<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('float_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // float owner
        $table->decimal('amount', 15, 2);
        $table->enum('action', ['added', 'expensed']);
        $table->decimal('balance_before', 15, 2);
        $table->decimal('balance_after', 15, 2);
        $table->text('description')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('float_transactions');
    }
};
