<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LoginHistory::class);
        $query = LoginHistory::query()->with('user');

        if ($search = trim((string) $request->string('search')->toString())) {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('successful_login')) {
            $query->where('successful_login', $request->boolean('successful_login'));
        }

        return Inertia::render('admin/LoginHistory/Index', [
            'histories' => $query->latest('login_at')->paginate(30)->withQueryString(),
            'filters' => $request->only(['search', 'successful_login']),
            'summary' => [
                'total' => LoginHistory::count(),
                'successful' => LoginHistory::where('successful_login', true)->count(),
                'failed' => LoginHistory::where('successful_login', false)->count(),
                'today' => LoginHistory::whereDate('login_at', now()->toDateString())->count(),
            ],
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $this->authorize('viewAny', LoginHistory::class);
        $query = LoginHistory::query()->with('user');

        if ($search = trim((string) $request->string('search')->toString())) {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('successful_login')) {
            $query->where('successful_login', $request->boolean('successful_login'));
        }

        $histories = $query->latest('login_at')->get();
        $filename = 'login-history-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($histories): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'User',
                'Email',
                'Login At',
                'Logout At',
                'IP Address',
                'Browser',
                'Platform',
                'Device',
                'Successful',
                'Failure Reason',
            ]);

            foreach ($histories as $history) {
                fputcsv($output, [
                    trim(($history->user?->first_name ?? '') . ' ' . ($history->user?->last_name ?? '')),
                    $history->user?->email,
                    optional($history->login_at)->toDateTimeString(),
                    optional($history->logout_at)->toDateTimeString(),
                    $history->ip_address,
                    $history->browser,
                    $history->platform,
                    $history->device,
                    $history->successful_login ? 'Yes' : 'No',
                    $history->failure_reason,
                ]);
            }

            fclose($output);
        }, $filename, $headers);
    }

    public function show(LoginHistory $loginHistory): JsonResponse
    {
        $this->authorize('view', $loginHistory);
        $loginHistory->load('user');

        return response()->json([
            'login_history' => [
                'id' => $loginHistory->id,
                'user' => $loginHistory->user,
                'login_at' => $loginHistory->login_at,
                'logout_at' => $loginHistory->logout_at,
                'ip_address' => $loginHistory->ip_address,
                'browser' => $loginHistory->browser,
                'platform' => $loginHistory->platform,
                'device' => $loginHistory->device,
                'successful_login' => $loginHistory->successful_login,
                'failure_reason' => $loginHistory->failure_reason,
            ],
        ]);
    }
}
