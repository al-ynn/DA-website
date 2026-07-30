<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use App\Models\ReportHistory;
use App\Services\AuditLogService;
use App\Services\BackendMetricsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Report::class);
        $query = Report::query()->where('is_draft', false)->with(['user', 'samples']);

        if ($search = trim((string) $request->string('search')->toString())) {
            $query->where(function (Builder $subQuery) use ($search): void {
                $subQuery
                    ->where('request_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('rsbsa_no', 'like', "%{$search}%");
            });
        }

        if ($status = trim((string) $request->string('status')->toString())) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($dateRange = trim((string) $request->string('date_range')->toString())) {
            $today = now()->startOfDay();

            match ($dateRange) {
                'today' => $query->whereDate('date', $today),
                'week' => $query->where('date', '>=', now()->subDays(7)->toDateString()),
                'month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
                'quarter' => $query->where('date', '>=', now()->startOfQuarter()->toDateString()),
                'year' => $query->whereYear('date', now()->year),
                default => null,
            };
        }

        $sort = $request->string('sort_by')->toString() ?: 'date-desc';
        $sortMap = [
            'id-desc' => ['id', 'desc'],
            'id-asc' => ['id', 'asc'],
            'date-desc' => ['date', 'desc'],
            'date-asc' => ['date', 'asc'],
            'client-asc' => ['full_name', 'asc'],
            'client-desc' => ['full_name', 'desc'],
            'company-asc' => ['company_name', 'asc'],
            'company-desc' => ['company_name', 'desc'],
        ];

        [$sortColumn, $sortDirection] = $sortMap[$sort] ?? $sortMap['date-desc'];
        $reports = $query
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total' => Report::where('is_draft', false)->count(),
            'pending' => Report::where('is_draft', false)->whereIn('status', ['Test Request submitted', 'Under Analyzation', 'Under Recommendation'])->count(),
            'completed' => Report::where('is_draft', false)->whereIn('status', ['Ready', 'Certified', 'Noted'])->count(),
            'processing' => Report::where('is_draft', false)->whereIn('status', ['Analyzed', 'Recommended', 'Reviewed'])->count(),
            'rejected' => Report::where('is_draft', false)->where('status', 'Rejected')->count(),
            'today' => Report::where('is_draft', false)->whereDate('date', now()->toDateString())->count(),
            'month' => Report::where('is_draft', false)->whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ];

        return Inertia::render('admin/Reports/index', [
            'reports' => $reports,
            'summary' => $summary,
            'filters' => $request->only(['search', 'status', 'date_range', 'sort_by']),
        ]);
    }

    public function show(Report $report): Response
    {
        $this->authorize('view', $report);
        $report->load(['user', 'samples']);

        return Inertia::render('admin/TestReports/Create/Page3', [
            'draftReport' => $report,
            'viewOnly' => true,
        ]);
    }

    public function detail(Report $report): JsonResponse
    {
        $this->authorize('view', $report);
        $report->load(['user', 'samples']);

        return response()->json([
            'report' => $report,
            'summary' => [
                'sample_count' => $report->samples->count(),
                'total_amount_due' => $report->total_amount_due,
                'deposit' => $report->deposit,
                'balance' => $report->balance,
            ],
            'samples' => $report->samples->map(function ($sample): array {
                return [
                    'id' => $sample->id,
                    'laboratory_code' => $sample->laboratory_code,
                    'sample_id' => $sample->sample_id,
                    'sample_description' => $sample->sample_description,
                    'sample_type' => $sample->sample_type,
                    'analysis_requested' => $sample->analysis_requested,
                    'subtotal' => $sample->subtotal,
                ];
            })->values(),
        ]);
    }

    public function nextRequestCode(): JsonResponse
    {
        $this->authorize('create', Report::class);

        return response()->json([
            'request_code' => $this->generateRequestCode(),
        ]);
    }

    public function currentDraft(Request $request): JsonResponse
    {
        $this->authorize('create', Report::class);

        $draft = Report::query()
            ->with('samples')
            ->where('user_id', $request->user()?->id)
            ->where('is_draft', true)
            ->latest('updated_at')
            ->latest('id')
            ->first();

        return response()->json([
            'draft' => $draft,
        ]);
    }

    public function saveDraft(StoreReportRequest $request): JsonResponse
    {
        $this->authorize('create', Report::class);

        $validated = $request->validated();
        $validated['user_id'] = $request->user()?->id;
        $draftId = $validated['draft_id'] ?? null;

        $report = DB::transaction(function () use ($request, $validated, $draftId): Report {
            $existingDraft = Report::query()
                ->where('user_id', $request->user()?->id)
                ->where('is_draft', true)
                ->when($draftId, fn (Builder $query) => $query->whereKey($draftId))
                ->latest('updated_at')
                ->latest('id')
                ->first();

            $data = $this->buildReportData($validated, true);

            if ($existingDraft) {
                $existingDraft->update($data);
                $report = $existingDraft;
            } else {
                $report = Report::create($data);
            }

            $this->syncSamples($report, $request->validated('samples', []));

            ReportHistory::create([
                'report_id' => $report->id,
                'user_id' => $request->user()?->id,
                'admin_id' => $request->user()?->id,
                'action' => 'draft_saved',
                'status' => $report->status,
                'new_value' => $report->fresh()->load('samples')->toArray(),
            ]);

            return $report->fresh()->load('samples');
        });

        return response()->json([
            'report' => $report,
        ]);
    }

    public function store(StoreReportRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('create', Report::class);
        DB::transaction(function () use ($request): void {
            $data = $this->buildReportData($request->validated(), $request->boolean('is_draft'));
            $report = Report::create($data);
            $this->syncSamples($report, $request->validated('samples', []));
            ReportHistory::create([
                'report_id' => $report->id,
                'user_id' => $request->user()?->id,
                'admin_id' => $request->user()?->id,
                'action' => 'created',
                'status' => $report->status,
                'new_value' => $report->toArray(),
            ]);

            app(AuditLogService::class)->record(
                action: 'report_created',
                subject: $report,
                newValue: $report->fresh()->load('samples')->toArray(),
                request: $request,
                userId: $request->user()?->id,
            );
        });

        app(BackendMetricsService::class)->forgetDashboardMetrics();

        return back()->with('success', 'Report created successfully.');
    }

    public function update(UpdateReportRequest $request, Report $report, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('update', $report);
        DB::transaction(function () use ($request, $report): void {
            $old = $report->toArray();
            $data = $this->buildReportData($request->validated(), $request->boolean('is_draft'));
            $report->update($data);
            $this->syncSamples($report, $request->validated('samples', []));
            ReportHistory::create([
                'report_id' => $report->id,
                'user_id' => $request->user()?->id,
                'admin_id' => $request->user()?->id,
                'action' => $old['status'] !== $report->status ? 'status_changed' : 'edited',
                'status' => $report->status,
                'old_value' => $old,
                'new_value' => $report->fresh()->toArray(),
            ]);

            app(AuditLogService::class)->record(
                action: 'report_updated',
                subject: $report,
                oldValue: $old,
                newValue: $report->fresh()->load('samples')->toArray(),
                request: $request,
                userId: $request->user()?->id,
            );
        });

        app(BackendMetricsService::class)->forgetDashboardMetrics();

        return back()->with('success', 'Report updated successfully.');
    }

    public function destroy(Report $report, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('delete', $report);
        $old = $report->load('samples')->toArray();

        DB::transaction(function () use ($report, $old, $auditLog): void {
            $report->samples()->delete();
            $report->delete();

            $auditLog->record(
                action: 'report_deleted',
                subject: $report,
                oldValue: $old,
                request: request(),
                userId: request()->user()?->id,
            );
        });

        app(BackendMetricsService::class)->forgetDashboardMetrics();

        return back()->with('success', 'Report deleted successfully.');
    }

    private function syncSamples(Report $report, array $samples): void
    {
        $report->samples()->delete();
        if ($samples === []) return;

        $normalizedSamples = collect($samples)
            ->map(function (array $sample): array {
                return [
                    'laboratory_code' => $sample['laboratory_code'],
                    'sample_id' => $sample['sample_id'],
                    'sample_description' => $sample['sample_description'] ?? null,
                    'sample_type' => $sample['sample_type'] ?? null,
                    'soil_condition' => $sample['soil_condition'] ?? null,
                    'soil_color' => $sample['soil_color'] ?? null,
                    'soil_depth' => $sample['soil_depth'] ?? null,
                    'soil_others' => $sample['soil_others'] ?? null,
                    'water_filtered' => $sample['water_filtered'] ?? null,
                    'water_temperature' => $sample['water_temperature'] ?? null,
                    'water_others' => $sample['water_others'] ?? null,
                    'topography' => $sample['topography'] ?? null,
                    'coordinates' => $sample['coordinates'] ?? null,
                    'longitude' => $sample['longitude'] ?? null,
                    'latitude' => $sample['latitude'] ?? null,
                    'region' => $sample['region'] ?? null,
                    'province' => $sample['province'] ?? null,
                    'municipality' => $sample['municipality'] ?? null,
                    'barangay' => $sample['barangay'] ?? null,
                    'farm_area' => $sample['farm_area'] ?? null,
                    'crops' => $sample['crops'] ?? null,
                    'remarks' => $sample['remarks'] ?? null,
                    'analysis_requested' => $sample['analysis_requested'] ?? null,
                    'analysis_requested_chemist' => $sample['analysis_requested_chemist'] ?? null,
                    'analysis_requested_agriculturist' => $sample['analysis_requested_agriculturist'] ?? null,
                    'subtotal' => $sample['subtotal'] ?? 0,
                ];
            })
            ->all();

        $report->samples()->createMany($normalizedSamples);
    }

    private function buildReportData(array $data, bool $isDraft = false): array
    {
        $samples = collect($data['samples'] ?? []);
        $sampleCount = $samples->count();
        $totalAmountDue = $samples->sum(fn (array $sample) => (float) ($sample['subtotal'] ?? 0));
        $requestCode = $data['request_code'] ?? $this->generateRequestCode();
        $classification = $data['classification'] ?? null;

        if (is_array($classification)) {
            $classification = implode(', ', array_filter(array_map('strval', $classification)));
        }

        return [
            'user_id' => $data['user_id'] ?? auth()->id(),
            'request_code' => $requestCode,
            'date' => $data['date'] ?? now()->toDateString(),
            'status' => $isDraft ? 'draft' : ($data['status'] ?? 'Test Request submitted'),
            'is_draft' => $isDraft,
            'surname' => $data['surname'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'full_name' => $data['full_name'] ?? null,
            'rsbsa_no' => $data['rsbsa_no'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'classification' => $classification,
            'student_type' => $data['student_type'] ?? null,
            'sex' => $data['sex'] ?? null,
            'age' => $data['age'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'email_address' => $data['email_address'] ?? null,
            'sampling_date' => $data['sampling_date'] ?? null,
            'sampling_time' => $data['sampling_time'] ?? null,
            'mode_of_release' => $data['mode_of_release'] ?? null,
            'retrieve_sample' => in_array($data['retrieve_sample'] ?? false, [true, 1, '1', 'yes'], true),
            'agreed_release_date' => $data['agreed_release_date'] ?? null,
            'number_of_samples' => $data['number_of_samples'] ?? $sampleCount,
            'date_received' => $data['date_received'] ?? null,
            'received_by' => $data['received_by'] ?? null,
            'payment_status' => $data['payment_status'] ?? null,
            'deposit' => $data['deposit'] ?? 0,
            'or_no' => $data['or_no'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'balance' => $data['balance'] ?? max($totalAmountDue - (float) ($data['deposit'] ?? 0), 0),
            'total_amount_due' => $totalAmountDue,
            'total_amount' => $data['total_amount'] ?? $totalAmountDue,
        ];
    }

    private function generateRequestCode(): string
    {
        $year = now()->year;
        $latestCode = Report::query()
            ->whereYear('date', $year)
            ->where('request_code', 'like', "RSL-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('request_code');

        $nextNumber = 1;

        if (is_string($latestCode) && preg_match('/^RSL-\d{4}-(\d{3})$/', $latestCode, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = Report::query()
                ->whereYear('date', $year)
                ->count();

            $nextNumber = $count + 1;
        }

        return sprintf('RSL-%d-%03d', $year, $nextNumber);
    }
}
