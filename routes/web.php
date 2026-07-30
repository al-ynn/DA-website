<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\ActiveSessionController;
use App\Http\Controllers\Admin\UserDashboardController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\PasswordReminderController;
use App\Http\Controllers\Admin\TaskPresetController;
use App\Http\Controllers\Admin\UserTaskPresetController;
use App\Http\Controllers\Admin\TaskManagementController;
use App\Models\Report;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::redirect('/', '/login');

Route::middleware(['auth', 'account.status', 'force.password.change'])->group(function () {
    Route::redirect('/profile', '/settings/profile');
    Route::redirect('/admin/todo', '/todo');
    Route::redirect('/users', '/user-management');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/todo', function () {
        $user = request()->user();

        if (! $user) {
            abort(403);
        }

        $user = User::query()
            ->with([
                'assignedTasks.category',
                'reports' => fn ($query) => $query->latest('date')->latest('id')->limit(10),
                'loginHistories' => fn ($query) => $query->latest('login_at')->limit(10),
            ])
            ->findOrFail($user->id);

        $assignedTasks = $user->assignedTasks->values();

        $todoRequests = $assignedTasks->map(function ($task, $index) use ($user, $assignedTasks) {
            $year = now()->format('y');
            $sampleDescription = 'Soil';
            $prefix = 'S';

            if ($task->name === 'Fertilizer Recommendation') {
                $sampleDescription = 'Fertilizer';
                $prefix = 'F';
            } elseif (in_array($task->name, ['Soil Moisture', 'Soil Texture', 'Particle Size Analysis'], true)) {
                $sampleDescription = 'Soil';
                $prefix = 'S';
            }

            if (in_array($task->name, ['pH', 'EC Analysis', 'Organic Matter Analysis', 'Available Phosphorus', 'Potassium', 'Calcium', 'Magnesium', 'Sodium', 'Zinc', 'Copper', 'Iron', 'Manganese'], true)) {
                $sampleDescription = 'Soil';
                $prefix = 'S';
            }

            $samePrefixCount = $assignedTasks->take($index + 1)->filter(function ($item) use ($prefix): bool {
                return match (true) {
                    $item->name === 'Fertilizer Recommendation' => $prefix === 'F',
                    in_array($item->name, ['Soil Moisture', 'Soil Texture', 'Particle Size Analysis'], true) => $prefix === 'S',
                    default => $prefix === 'S',
                };
            })->count();

            return [
                'id' => (string) $task->id,
                'code' => "RSL-" . now()->year . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'labCode' => "{$prefix}" . $year . '-' . str_pad((string) $samePrefixCount, 3, '0', STR_PAD_LEFT),
                'testRequestCode' => "RSL-" . now()->year . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'taskType' => $task->name,
                'dueDate' => optional($user->updated_at)->toDateString() ?? now()->toDateString(),
                'role' => strtolower($task->category?->name ?? $user->role),
                'sampleDescription' => $sampleDescription,
                'status' => 'assigned',
                'assignedTo' => (string) $user->id,
                'assignedToName' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                'resultValue' => null,
                'resultRemarks' => null,
            ];
        })->values();

        return Inertia::render('admin/ToDo/index', [
            'authUser' => [
                'id' => $user->id,
                'name' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                'role' => $user->role,
                'roles' => [$user->role],
                'assignedTasks' => $user->assignedTasks->pluck('name')->values(),
            ],
            'todoRequests' => $todoRequests,
            'tasks' => $user->assignedTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'category' => $task->category?->name ? strtolower($task->category->name) : null,
                ];
            })->values(),
            'latestReports' => $user->reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'date' => $report->date,
                    'status' => $report->status,
                    'request_code' => $report->request_code,
                ];
            })->values(),
            'latestLogins' => $user->loginHistories->map(function ($login) {
                return [
                    'id' => $login->id,
                    'login_at' => $login->login_at,
                ];
            })->values(),
        ]);
    })->name('todo');

    Route::get('/todo/available-tasks', function () {
        $user = request()->user();

        if (! $user) {
            abort(403);
        }

        return Inertia::render('admin/ToDo/AvailableTasks', [
            'authUser' => [
                'id' => $user->id,
                'name' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                'role' => $user->role,
                'roles' => [$user->role],
                'assignedTasks' => $user->assignedTasks()->pluck('name')->values(),
            ],
            'todoRequests' => $user->assignedTasks()->get()->map(function ($task) use ($user) {
                return [
                    'id' => (string) $task->id,
                    'code' => null,
                    'labCode' => null,
                    'testRequestCode' => null,
                    'taskType' => $task->name,
                    'dueDate' => optional($user->updated_at)->toDateString() ?? now()->toDateString(),
                    'role' => strtolower($task->category?->name ?? $user->role),
                    'sampleDescription' => 'Soil',
                    'status' => 'assigned',
                    'assignedTo' => (string) $user->id,
                    'assignedToName' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                    'resultValue' => null,
                    'resultRemarks' => null,
                ];
            })->values(),
            'taskType' => request()->string('taskType')->toString(),
        ]);
    })->name('todo.available-tasks');

    Route::get('/todo/available-tasks/{taskType}', function (string $taskType) {
        $user = request()->user();

        if (! $user) {
            abort(403);
        }

        $assignedTasks = $user->assignedTasks->values();
        $todoRequests = $assignedTasks->map(function ($task, $index) use ($user, $assignedTasks) {
            $year = now()->format('y');
            $prefix = $task->name === 'Fertilizer Recommendation' ? 'F' : 'S';
            $sampleDescription = $prefix === 'F' ? 'Fertilizer' : 'Soil';
            $samePrefixCount = $assignedTasks->take($index + 1)->filter(function ($item) use ($prefix): bool {
                return $prefix === 'F'
                    ? $item->name === 'Fertilizer Recommendation'
                    : $item->name !== 'Fertilizer Recommendation';
            })->count();

            return [
                'id' => (string) $task->id,
                'code' => "RSL-" . now()->year . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'labCode' => "{$prefix}" . $year . '-' . str_pad((string) $samePrefixCount, 3, '0', STR_PAD_LEFT),
                'testRequestCode' => "RSL-" . now()->year . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'taskType' => $task->name,
                'dueDate' => optional($user->updated_at)->toDateString() ?? now()->toDateString(),
                'role' => strtolower($task->category?->name ?? $user->role),
                'sampleDescription' => $sampleDescription,
                'status' => 'assigned',
                'assignedTo' => (string) $user->id,
                'assignedToName' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                'resultValue' => null,
                'resultRemarks' => null,
            ];
        })->values();

        return Inertia::render('admin/ToDo/AvailableTasks', [
            'authUser' => [
                'id' => $user->id,
                'name' => trim(collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->join(' ')),
                'role' => $user->role,
                'roles' => [$user->role],
                'assignedTasks' => $user->assignedTasks()->pluck('name')->values(),
            ],
            'todoRequests' => $todoRequests,
            'taskType' => $taskType,
        ]);
    })->name('todo.available-tasks.task');

    Route::get('/todo/task/admin/{role}/{request}', function (string $role, string $request) {
        $role = strtolower($role);

        if (! in_array($role, ['reviewer', 'certifier', 'noter'], true)) {
            abort(404);
        }

        return Inertia::render("admin/ToDo/Task/admin/{$role}/id", [
            'requestId' => $request,
        ]);
    })->name('todo.task.admin');

    /*
    |--------------------------------------------------------------------------
    | Password Reminder
    |--------------------------------------------------------------------------
    */

    Route::patch(
        '/password-reminder/dismiss',
        [PasswordReminderController::class, 'dismiss']
    )->name('password-reminder.dismiss');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('user-management')
    ->name('user-management.')
    ->group(function () {

        Route::get('/', [UserManagementController::class, 'index'])
            ->name('index');

        Route::get('/create', [UserManagementController::class, 'create'])
            ->name('create');

        Route::post('/', [UserManagementController::class, 'store'])
            ->name('store');

        // Static routes FIRST
        Route::get('/disabled', [UserManagementController::class, 'disabled'])
            ->name('disabled');

        Route::get('/drafts', [UserManagementController::class, 'drafts'])
            ->name('drafts');

        // Parameter routes LAST
        Route::get('/{user}', [UserManagementController::class, 'show'])
            ->name('show');

        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('edit');

        Route::put('/{user}', [UserManagementController::class, 'update'])
            ->name('update');

        Route::patch('/{user}/disable', [UserManagementController::class, 'disable'])
            ->name('disable');

        Route::patch('/{user}/enable', [UserManagementController::class, 'enable'])
            ->name('enable');

        Route::patch('/{user}/approve', [UserManagementController::class, 'approve'])
            ->name('approve');

        Route::patch('/{user}/disapprove', [UserManagementController::class, 'disapprove'])
            ->name('disapprove');
    });

    Route::middleware('auth')->group(function () {
        Route::patch(
            '/password-reminder/dismiss',
            [PasswordReminderController::class, 'dismiss']
        )->name('password-reminder.dismiss');
    });

    Route::middleware(['auth', 'role:admin'])
        ->prefix('task-management')
        ->name('task-management.')
        ->group(function () {

            Route::get('/', [TaskManagementController::class, 'index'])
                ->name('index');

              Route::post('/presets/clear', [TaskPresetController::class, 'clear'])
                  ->name('presets.clear');

            Route::resource('presets', TaskPresetController::class);

            Route::match(['put', 'post'],
                '/users/{user}/task-presets',
                [UserTaskPresetController::class, 'update']
            )->name('users.task-presets.update');

            Route::match(['put', 'post'],
                '/users/{user}/task-preset',
                [UserTaskPresetController::class, 'update']
            )->name('users.task-preset.update');

            Route::match(['put', 'post'],
                '/users/task-presets',
                [UserTaskPresetController::class, 'bulkUpdate']
            )->name('users.task-presets.bulk-update');
        });

    Route::middleware(['auth', 'role:admin'])
        ->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports');
            Route::get('/user-dashboard', [UserDashboardController::class, 'index'])->name('user-dashboard');
            Route::get('/login-history', [LoginHistoryController::class, 'index'])->name('login-history');
            Route::get('/login-history/{loginHistory}', [LoginHistoryController::class, 'show'])->name('login-history.show');
            Route::get('/login-history/export', [LoginHistoryController::class, 'export'])->name('login-history.export');
            Route::get('/active-sessions', [ActiveSessionController::class, 'index'])->name('active-sessions');
            Route::delete('/active-sessions/{activeSession}', [ActiveSessionController::class, 'destroy'])->name('active-sessions.destroy');
            Route::get('/sms', function () {
                return Inertia::render('admin/SMS/index');
            })->name('sms');
        });

    Route::middleware(['auth', 'role:admin'])
        ->prefix('test-reports/create')
        ->group(function () {
            Route::get('/', function () {
                return redirect('/test-reports/create/page-1');
            });

            Route::get('/page-1', function () {
                $draftId = request()->integer('draft_id');
                $query = Report::query()
                    ->with('samples')
                    ->where('is_draft', true)
                    ->where('user_id', request()->user()?->id);

                if ($draftId) {
                    $query->whereKey($draftId);
                }

                return Inertia::render('admin/TestReports/Create/Page1', [
                    'draftReport' => $query->latest('updated_at')->latest('id')->first(),
                ]);
            });

            Route::get('/page-2', function () {
                $draftId = request()->integer('draft_id');
                $query = Report::query()
                    ->with('samples')
                    ->where('is_draft', true)
                    ->where('user_id', request()->user()?->id);

                if ($draftId) {
                    $query->whereKey($draftId);
                }

                return Inertia::render('admin/TestReports/Create/Page2', [
                    'client_id' => request()->integer('client_id') ?: null,
                    'from_existing' => request()->boolean('from_existing'),
                    'draftReport' => $query->latest('updated_at')->latest('id')->first(),
                ]);
            });

            Route::get('/page-3', function () {
                $draftId = request()->integer('draft_id');
                $query = Report::query()
                    ->with('samples')
                    ->where('is_draft', true)
                    ->where('user_id', request()->user()?->id);

                if ($draftId) {
                    $query->whereKey($draftId);
                }

                return Inertia::render('admin/TestReports/Create/Page3', [
                    'draftReport' => $query->latest('updated_at')->latest('id')->first(),
                ]);
            });
        });

    Route::middleware(['auth', 'role:admin'])
        ->prefix('reports')
        ->name('reports.')
        ->group(function () {
            Route::get('/next-request-code', [ReportController::class, 'nextRequestCode'])->name('next-request-code');
            Route::get('/current-draft', [ReportController::class, 'currentDraft'])->name('current-draft');
            Route::post('/draft', [ReportController::class, 'saveDraft'])->name('draft.store');
            Route::put('/{report}/draft', [ReportController::class, 'update'])->name('draft.update');
            Route::get('/{report}/detail', [ReportController::class, 'detail'])->name('detail');
            Route::get('/{report}', [ReportController::class, 'show'])->name('show');
            Route::post('/', [ReportController::class, 'store'])->name('store');
            Route::put('/{report}', [ReportController::class, 'update'])->name('update');
            Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
        });

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
