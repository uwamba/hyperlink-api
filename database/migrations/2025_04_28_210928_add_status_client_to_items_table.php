<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->enum('status', ['in_stock', 'delivered', 'reserved'])->default('in_stock')->after('brand');
            $table->unsignedBigInteger('client_id')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('client_id');


        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['status', 'client_id', 'delivered_at']);
        });
    }
};
