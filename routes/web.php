<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\PasswordReminderController;
use App\Http\Controllers\Admin\TaskPresetController;
use App\Http\Controllers\Admin\UserTaskPresetController;
use App\Http\Controllers\Admin\TaskManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    Route::redirect('/profile', '/settings/profile');

    Route::get('/todo', function () {
        return Inertia::render('admin/ToDo/index');
    })->name('todo');

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

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
