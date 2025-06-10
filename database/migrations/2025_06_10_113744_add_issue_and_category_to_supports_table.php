<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->string('issue')->nullable()->after('address');
            $table->string('category')->nullable()->after('issue');
        });
    }

    public function down(): void
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->dropColumn(['issue', 'category']);
        });
    }
};
