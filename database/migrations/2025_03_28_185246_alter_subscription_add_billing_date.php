<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->date('billing_date')->nullable();
        });

        // Set the default billing_date to the last day of the month
        DB::statement("
            UPDATE subscriptions 
            SET billing_date = LAST_DAY(NOW())
        ");
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('billing_date');
        });
    }
};
