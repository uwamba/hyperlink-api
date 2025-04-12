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
    Schema::table('supports', function (Blueprint $table) {
        $table->string('user_id')->default('active');  // Add the status field with a default value
    });
}

public function down()
{
    Schema::table('supports', function (Blueprint $table) {
        $table->dropColumn('user_id');
    });
}
};
