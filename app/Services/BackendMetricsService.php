<?php

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\Report;
use App\Models\ReportSample;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BackendMetricsService
{
    private const CACHE_KEY = 'dashboard.metrics.v1';

    public function dashboardMetrics(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $users = User::query()->get();

            return [
                'totalUsers' => $users->count(),
                'activeUsers' => $users->where('is_disabled', false)->where('is_draft', false)->count(),
                'disabledUsers' => $users->where('is_disabled', true)->count(),
                'draftUsers' => $users->where('is_draft', true)->count(),
                'administrators' => $users->where('role', 'admin')->count(),
                'chemists' => $users->where('role', 'chemist')->count(),
                'agriculturists' => $users->where('role', 'agriculturist')->count(),
                'totalReports' => Report::count(),
                'pendingReports' => Report::whereIn('status', ['Test Request submitted', 'Under Analyzation', 'Under Recommendation'])->count(),
                'processingReports' => Report::whereIn('status', ['Analyzed', 'Recommended', 'Reviewed'])->count(),
                'completedReports' => Report::whereIn('status', ['Ready', 'Certified', 'Noted'])->count(),
                'rejectedReports' => Report::where('status', 'Rejected')->count(),
                'todayReports' => Report::whereDate('date', now()->toDateString())->count(),
                'monthReports' => Report::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
                'totalSamples' => ReportSample::count(),
                'successfulLogins' => LoginHistory::where('successful_login', true)->count(),
                'failedLogins' => LoginHistory::where('successful_login', false)->count(),
            ];
        });
    }

    public function forgetDashboardMetrics(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
