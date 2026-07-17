<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. (Link a preset to multiple laboratory tasks)
     */
    public function up(): void
    {
        Schema::create('task_preset_task', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_preset_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('laboratory_task_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'task_preset_id',
                'laboratory_task_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_preset_task');
    }
};
