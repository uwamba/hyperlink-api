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
    Schema::table('plans', function (Blueprint $table) {
        $table->string('provider_name')->nullable();
        $table->unsignedBigInteger('supplier_id')->nullable();
        $table->decimal('provider_price', 10, 2)->nullable();
    });
}

public function down()
{
    Schema::table('plans', function (Blueprint $table) {
        $table->dropColumn(['provider_name', 'supplier_id', 'provider_price']);
    });
}

};
