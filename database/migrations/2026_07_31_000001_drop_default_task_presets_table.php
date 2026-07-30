<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('default_task_presets')) {
            Schema::drop('default_task_presets');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('default_task_presets')) {
            return;
        }

        Schema::create('default_task_presets', function (Blueprint $table): void {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('tasks');
            $table->foreignId('task_category_id')
                ->nullable()
                ->constrained('task_categories')
                ->nullOnDelete();
            $table->json('laboratory_task_ids')->nullable();
            $table->timestamps();
        });
    }
};
