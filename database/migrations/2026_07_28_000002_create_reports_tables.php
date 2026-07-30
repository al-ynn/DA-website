<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_code')->unique();
            $table->date('date')->index();
            $table->string('status')->index();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('full_name')->nullable()->index();
            $table->string('rsbsa_no')->nullable()->index();
            $table->string('company_name')->nullable()->index();
            $table->string('classification')->nullable()->index();
            $table->string('sex')->nullable();
            $table->string('age')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->date('sampling_date')->nullable()->index();
            $table->time('sampling_time')->nullable();
            $table->string('mode_of_release')->nullable();
            $table->boolean('retrieve_sample')->default(false);
            $table->date('agreed_release_date')->nullable();
            $table->unsignedInteger('number_of_samples')->default(0);
            $table->date('date_received')->nullable()->index();
            $table->string('received_by')->nullable();
            $table->string('payment_status')->nullable()->index();
            $table->decimal('deposit', 12, 2)->default(0);
            $table->string('or_no')->nullable();
            $table->date('payment_date')->nullable()->index();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_amount_due', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('report_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('laboratory_code')->index();
            $table->string('sample_id')->index();
            $table->text('sample_description')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('topography')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('farm_area')->nullable();
            $table->string('crops')->nullable();
            $table->text('remarks')->nullable();
            $table->text('analysis_requested')->nullable();
            $table->text('analysis_requested_chemist')->nullable();
            $table->text('analysis_requested_agriculturist')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_samples');
        Schema::dropIfExists('reports');
    }
};
