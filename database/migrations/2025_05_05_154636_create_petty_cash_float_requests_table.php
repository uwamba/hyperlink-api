<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_xx_xx_create_petty_cash_float_requests_table.php

Schema::create('petty_cash_float_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 12, 2);
    $table->text('reason');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('approved_at')->nullable();
    $table->date('requested_for')->nullable(); // Optional: date the float is needed
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_float_requests');
    }
};
