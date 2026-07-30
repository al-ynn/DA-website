<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reports') && ! Schema::hasColumn('reports', 'student_type')) {
            Schema::table('reports', function (Blueprint $table): void {
                $table->string('student_type')->nullable()->after('classification');
            });
        }

        if (! Schema::hasTable('report_samples')) {
            return;
        }

        $columns = [
            'soil_condition',
            'soil_color',
            'soil_depth',
            'soil_others',
            'water_filtered',
            'water_temperature',
            'water_others',
        ];

        foreach ($columns as $column) {
            if (! Schema::hasColumn('report_samples', $column)) {
                Schema::table('report_samples', function (Blueprint $table) use ($column): void {
                    $table->string($column)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reports') && Schema::hasColumn('reports', 'student_type')) {
            Schema::table('reports', function (Blueprint $table): void {
                $table->dropColumn('student_type');
            });
        }

        if (! Schema::hasTable('report_samples')) {
            return;
        }

        $columns = [
            'soil_condition',
            'soil_color',
            'soil_depth',
            'soil_others',
            'water_filtered',
            'water_temperature',
            'water_others',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('report_samples', $column)) {
                Schema::table('report_samples', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
