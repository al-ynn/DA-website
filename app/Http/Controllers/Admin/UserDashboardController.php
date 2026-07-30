<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackendMetricsService;
use App\Models\LoginHistory;
use App\Models\ReportSample;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, BackendMetricsService $metricsService): Response
    {
        $this->authorize('access-admin-dashboard');
        $users = User::query()
            ->select('id', 'first_name', 'middle_name', 'last_name', 'suffix', 'role', 'additional_tasks', 'is_disabled', 'is_draft', 'updated_at')
            ->withCount([
                'loginHistories',
                'reports',
            ])
            ->get();

        $metrics = $metricsService->dashboardMetrics();

        $latestReports = Report::query()
            ->with('samples')
            ->latest('date')
            ->latest('id')
            ->limit(10)
            ->get();

        $latestLogins = LoginHistory::query()
            ->with('user')
            ->latest('login_at')
            ->limit(10)
            ->get();

        return Inertia::render('admin/UserDashboard/index', [
            'metrics' => $metrics,
            'latestReports' => $latestReports,
            'latestLogins' => $latestLogins,
            'users' => $users,
        ]);
    }
}
