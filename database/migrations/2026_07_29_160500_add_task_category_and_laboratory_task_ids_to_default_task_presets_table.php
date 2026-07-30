<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('default_task_presets')) {
            return;
        }

        Schema::table('default_task_presets', function (Blueprint $table): void {
            if (! Schema::hasColumn('default_task_presets', 'task_category_id')) {
                $table->foreignId('task_category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('task_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('default_task_presets', 'laboratory_task_ids')) {
                $table->json('laboratory_task_ids')
                    ->nullable()
                    ->after('description');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('default_task_presets')) {
            return;
        }

        Schema::table('default_task_presets', function (Blueprint $table): void {
            if (Schema::hasColumn('default_task_presets', 'task_category_id')) {
                $table->dropConstrainedForeignId('task_category_id');
            }

            if (Schema::hasColumn('default_task_presets', 'laboratory_task_ids')) {
                $table->dropColumn('laboratory_task_ids');
            }
        });
    }
};
