<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. (Stores presets created by admin)
     */
    public function up(): void
    {
        Schema::create('task_presets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('description')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_presets');
    }
};
