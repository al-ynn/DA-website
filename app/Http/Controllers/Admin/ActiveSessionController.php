<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActiveSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActiveSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ActiveSession::class);
        return Inertia::render('admin/LoginHistory/Index', [
            'activeSessions' => ActiveSession::query()
                ->with('user')
                ->latest('last_active_at')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function destroy(Request $request, ActiveSession $activeSession): RedirectResponse
    {
        $this->authorize('delete', $activeSession);
        $activeSession->update([
            'logout_at' => now(),
        ]);

        return back()->with('success', 'Session revoked successfully.');
    }
}
