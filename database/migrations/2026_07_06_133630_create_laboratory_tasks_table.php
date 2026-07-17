<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. (Stores every selectable checkbox)
     */
    public function up(): void
    {
        Schema::create('laboratory_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->timestamps();

            $table->unique([
                'task_category_id',
                'name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laboratory_tasks');
    }
};
