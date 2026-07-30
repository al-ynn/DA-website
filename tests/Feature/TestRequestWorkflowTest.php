<?php

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function testRequestPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'date' => now()->toDateString(),
        'status' => 'draft',
        'is_draft' => true,
        'surname' => 'Dela Cruz',
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'full_name' => 'Juan Santos Dela Cruz',
        'rsbsa_no' => 'R-100',
        'company_name' => 'Sample Farm',
        'classification' => 'Student',
        'student_type' => 'Undergraduate',
        'sex' => 'Male',
        'age' => 25,
        'address' => 'Cabanatuan City',
        'contact_number' => '+639171234567',
        'email_address' => 'juan@example.test',
        'sampling_date' => '2026-07-30',
        'sampling_time' => '17:00',
        'mode_of_release' => 'online',
        'retrieve_sample' => 'yes',
        'agreed_release_date' => '2026-08-05',
        'number_of_samples' => 1,
        'date_received' => '2026-07-30',
        'received_by' => 'System Administrator',
        'payment_status' => 'pending',
        'deposit' => 100,
        'or_no' => 'OR-100',
        'payment_date' => '2026-07-30',
        'balance' => 400,
        'samples' => [[
            'laboratory_code' => 'S26-001',
            'sample_id' => 'SAMPLE-1',
            'sample_description' => 'soil',
            'sample_type' => 'Composite',
            'soil_condition' => 'dry',
            'soil_color' => 'Brown',
            'soil_depth' => '15 cm',
            'soil_others' => 'None',
            'topography' => 'Flat',
            'longitude' => '120.967',
            'latitude' => '15.486',
            'region' => 'Region III',
            'province' => 'Nueva Ecija',
            'municipality' => 'Cabanatuan City',
            'barangay' => 'Aduas Centro',
            'farm_area' => '1 hectare',
            'crops' => 'Rice',
            'remarks' => 'Handle carefully',
            'analysis_requested' => 'pH, EC Analysis',
            'subtotal' => 500,
        ]],
    ], $overrides);
}

test('test request wizard persists and finalizes one database-backed draft', function () {
    $admin = User::create([
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'admin-workflow@example.test',
        'password' => 'password',
        'role' => 'admin',
        'is_draft' => false,
        'is_disabled' => false,
    ]);

    $this->actingAs($admin);

    $pageOne = testRequestPayload([
        'samples' => [],
        'number_of_samples' => 0,
    ]);

    $created = $this->postJson(route('reports.draft.store'), $pageOne)
        ->assertOk()
        ->assertJsonPath('report.surname', 'Dela Cruz')
        ->assertJsonPath('report.student_type', 'Undergraduate');

    $draftId = $created->json('report.id');

    $this->assertDatabaseHas('reports', [
        'id' => $draftId,
        'user_id' => $admin->id,
        'is_draft' => true,
        'surname' => 'Dela Cruz',
        'student_type' => 'Undergraduate',
    ]);

    $pageTwo = testRequestPayload([
        'draft_id' => $draftId,
        'request_code' => $created->json('report.request_code'),
    ]);

    $this->postJson(route('reports.draft.store'), $pageTwo)
        ->assertOk()
        ->assertJsonPath('report.id', $draftId)
        ->assertJsonCount(1, 'report.samples');

    expect(Report::query()->where('user_id', $admin->id)->count())->toBe(1);

    $this->assertDatabaseHas('report_samples', [
        'report_id' => $draftId,
        'sample_description' => 'soil',
        'soil_condition' => 'dry',
        'soil_color' => 'Brown',
        'soil_depth' => '15 cm',
        'analysis_requested' => 'pH, EC Analysis',
    ]);

    $this->get("/test-reports/create/page-3?draft_id={$draftId}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/TestReports/Create/Page3')
            ->where('draftReport.id', $draftId)
            ->where('draftReport.surname', 'Dela Cruz')
            ->where('draftReport.samples.0.soil_condition', 'dry')
            ->where('draftReport.samples.0.analysis_requested', 'pH, EC Analysis'));

    $draft = Report::query()->with('samples')->findOrFail($draftId);
    $finalPayload = testRequestPayload([
        'request_code' => $draft->request_code,
        'status' => 'Test Request submitted',
        'is_draft' => false,
    ]);

    $this->put(route('reports.update', $draft), $finalPayload)
        ->assertRedirect();

    $this->assertDatabaseHas('reports', [
        'id' => $draftId,
        'request_code' => $draft->request_code,
        'is_draft' => false,
        'status' => 'Test Request submitted',
        'surname' => 'Dela Cruz',
        'student_type' => 'Undergraduate',
        'total_amount_due' => 500,
    ]);

    $this->get(route('reports'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Reports/index')
            ->where('reports.data.0.id', $draftId)
            ->where('reports.data.0.request_code', $draft->request_code)
            ->where('reports.data.0.is_draft', false));

    $this->get(route('reports.show', $draft))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/TestReports/Create/Page3')
            ->where('draftReport.id', $draftId)
            ->where('draftReport.samples.0.sample_id', 'SAMPLE-1')
            ->where('draftReport.samples.0.analysis_requested', 'pH, EC Analysis')
            ->where('viewOnly', true));
});

test('wizard display routes do not accept write methods', function () {
    $admin = User::create([
        'first_name' => 'Route',
        'last_name' => 'Administrator',
        'email' => 'admin-routes@example.test',
        'password' => 'password',
        'role' => 'admin',
        'is_draft' => false,
        'is_disabled' => false,
    ]);

    $this->actingAs($admin);

    $this->get('/test-reports/create/page-1')->assertOk();
    $this->put('/test-reports/create/page-1', [])->assertMethodNotAllowed();
});
