
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $tables = [
        'assets',
        'users',
        'delivery_notes',
        'delivery_note_items',
        'billing',
        'clients',
        'expenses',
        'invoices',
        'items',
        'payments',
        'petty_cash_float_requests',
        'plans',
        'products',
        'purchases',
        'subscriptions',
        'suppliers',
        'supports',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('id');
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

                // Optional: add foreign key constraints if users table exists
                // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                // $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Drop foreign key constraints first if they exist
                // $table->dropForeign([$table . '_created_by_foreign']);
                // $table->dropForeign([$table . '_updated_by_foreign']);

                $table->dropColumn(['created_by', 'updated_by']);
            });
        }
    }
};
