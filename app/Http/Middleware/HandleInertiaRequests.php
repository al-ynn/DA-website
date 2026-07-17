<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user(),
            ],

            'mustChangePassword' => fn () =>
                $request->user()
                    ? is_null($request->user()->password_changed_at)
                    : false,

            'passwordReminderDismissed' => fn () =>
                $request->user()
                    ? !is_null($request->user()->password_reminder_dismissed_at)
                    : false,

            // Added: hides the reminder modal for the current login session
            'passwordReminderHidden' => fn () =>
                $request->session()->get('password_reminder_hidden', false),
        ];
    }
}