<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportSample;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user) {
            return;
        }

        $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
        $firstName = $user->first_name ?: ($nameParts[0] ?? 'System');
        $lastName = $user->last_name ?: ($nameParts[count($nameParts) - 1] ?? 'Administrator');
        $middleName = $user->middle_name ?: null;

        $report = Report::updateOrCreate(
            ['request_code' => 'RSL-2026-0001'],
            [
                'user_id' => $user->id,
                'date' => now()->subDays(2)->toDateString(),
                'status' => 'Ready',
                'surname' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'full_name' => trim(collect([$firstName, $middleName, $lastName])->filter()->join(' ')),
                'company_name' => 'DeptAgri',
                'classification' => 'Farmer',
                'sex' => 'Male',
                'age' => '35',
                'address' => 'Department of Agriculture',
                'contact_number' => '+639171234567',
                'email_address' => $user->email,
                'sampling_date' => now()->subDays(3)->toDateString(),
                'sampling_time' => '08:00 AM',
                'mode_of_release' => 'Pick up',
                'retrieve_sample' => true,
                'agreed_release_date' => now()->addDays(7)->toDateString(),
                'number_of_samples' => 1,
                'date_received' => now()->subDays(2)->toDateString(),
                'received_by' => 'Sample Receiver',
                'payment_status' => 'Paid',
                'total_amount_due' => 1500,
                'total_amount' => 1500,
                'deposit' => 500,
                'balance' => 0,
            ]
        );

        ReportSample::updateOrCreate(
            [
                'report_id' => $report->id,
                'laboratory_code' => 'LAB-01',
                'sample_id' => 'S-001',
            ],
            [
                'sample_description' => 'Soil sample',
                'sample_type' => 'Soil',
                'subtotal' => 1500,
                'analysis_requested' => 'pH, EC Analysis',
            ]
        );
    }
}
