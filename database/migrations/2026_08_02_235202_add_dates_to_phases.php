<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            if (! Schema::hasColumn('phases', 'start_date')) {
                $table->date('start_date')->nullable()->after('sort_order');
            }
            if (! Schema::hasColumn('phases', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('phases', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
            $table->dropSoftDeletes();
        });
    }
};
