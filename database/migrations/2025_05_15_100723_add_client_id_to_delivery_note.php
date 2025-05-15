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
  Schema::table('delivery_notes', function (Blueprint $table) {
    $table->uuid('client_id')
          ->default('0195dc01-0655-70b1-89d3-f0d392e759f5');

    $table->foreign('client_id')
          ->references('id')
          ->on('clients')
          ->onDelete('cascade');
});




}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note', function (Blueprint $table) {
            //
        });
    }
};
