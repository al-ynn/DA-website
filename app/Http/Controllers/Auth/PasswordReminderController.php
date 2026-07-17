<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordReminderController extends Controller
{
    /**
     * User clicked "Remind Me Later".
     */
    public function dismiss(Request $request): RedirectResponse
    {
        $request->user()->update([
            'password_reminder_dismissed_at' => now(),
        ]);

        // Hide the reminder modal for the current login session
        $request->session()->put('password_reminder_hidden', true);

        return back();
    }
}