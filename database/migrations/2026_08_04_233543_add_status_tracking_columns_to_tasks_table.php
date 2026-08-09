// database/migrations/2026_08_05_000000_add_status_tracking_columns_to_tasks_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add status tracking columns if they don't exist
            if (! Schema::hasColumn('tasks', 'status_updated_at')) {
                $table->timestamp('status_updated_at')->nullable()->after('updated_at');
            }

            if (! Schema::hasColumn('tasks', 'status_changed_by')) {
                $table->foreignId('status_changed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('status_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'status_updated_at')) {
                $table->dropColumn('status_updated_at');
            }
            if (Schema::hasColumn('tasks', 'status_changed_by')) {
                $table->dropForeign(['status_changed_by']);
                $table->dropColumn('status_changed_by');
            }
        });
    }
};
