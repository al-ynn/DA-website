<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();

            $table->string('sex')->nullable();
            $table->date('birthdate')->nullable();

            $table->string('contact_number')->nullable();
            $table->string('email')->nullable()->unique();

            $table->string('password');

            $table->timestamp('password_changed_at')->nullable();

            $table->timestamp('password_reminder_dismissed_at')->nullable();

            $table->string('role')->nullable();

            $table->json('additional_tasks')->nullable();

            $table->boolean('is_draft')->default(false);
            $table->boolean('is_disabled')->default(false);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
