<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;


class UserManagementController extends Controller
{
    /**
     * Active Accounts
     */
    public function index(): Response
    {
        return Inertia::render('admin/UserManagement/index', [
            'users' => User::select(
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'sex',
                'birthdate',
                'contact_number',
                'email',
                'role',
                'additional_tasks',
                'is_disabled',
                'created_at',
            )
                ->where('is_disabled', false)
                ->where('is_draft', false)
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    /**
     * Create Account Page
     */
    public function create(): Response
    {
        return Inertia::render('admin/UserManagement/CreateAccount');
    }

    /**
     * Save Account
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $tasks = collect($request->additional_tasks ?? [])
            ->unique()
            ->reject(fn ($task) => $task === 'admin')
            ->reject(fn ($task) => $task === $request->role)
            ->values()
            ->all();

        $temporaryPassword = null;

        if (! $request->boolean('is_draft')) {
            $temporaryPassword = match ($request->role) {
                'admin' => 'admin123',
                'chemist' => 'chemist123',
                'agriculturist' => 'agriculturist123',
                default => 'password123',
            };
        }
        
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,

            'email' => $request->email,
            'contact_number' => $request->contact_number,

            'sex' => $request->sex,
            'birthdate' => $request->birthdate,

            'password' => Hash::make($temporaryPassword),

            'password_changed_at' => null,

            'role' => $request->role,

            'additional_tasks' => $tasks,

            'is_disabled' => $request->boolean('is_disabled'),
            'is_draft' => $request->boolean('is_draft'),
        ]);

        return redirect()
            ->route('user-management.create')
            ->with([
                'success' => true,
                'is_draft' => $request->boolean('is_draft'),
                'temporary_password' => $temporaryPassword,
            ]);
    }

    /**
     * View Account
     */
    public function show(User $user): Response
    {
        return Inertia::render('admin/UserManagement/ViewAccount', [
            'user' => $user,
        ]);
    }

    /**
     * Edit Page
     */
    public function edit(User $user): Response
    {
        return Inertia::render('admin/UserManagement/EditAccount', [
            'user' => $user,
        ]);
    }

    /**
     * Update Account
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $tasks = collect($request->additional_tasks ?? [])
            ->unique()
            ->reject(fn ($task) => $task === 'admin')
            ->reject(fn ($task) => $task === $request->role)
            ->values()
            ->all();

        $user->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'sex' => $request->sex,
            'birthdate' => $request->birthdate,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'role' => $request->role,
            'additional_tasks' => $tasks,

            'is_disabled' => $request->boolean('is_disabled'),
            'is_draft' => false,
        ]);

        return redirect()
            ->route('user-management.index')
            ->with('success', 'Account updated successfully.');
    }


    /**
     * Disable Account 
     */
    public function disable(User $user): RedirectResponse
    {
        $user->update([
            'is_disabled' => true,
        ]);

        return redirect()
            ->route('user-management.disabled')
            ->with('success', 'Account disabled successfully.');
    }

    /**
     * Enable Account
     */
    public function enable(User $user): RedirectResponse
    {
        $user->update([
            'is_disabled' => false,
        ]);

        return redirect()
            ->route('user-management.disabled')
            ->with('success', 'Account enabled successfully.');
    }

    /**
     * Approve Draft Account
     */
    public function approve(User $user): RedirectResponse
    {
        $missingFields = [];

        if (blank($user->first_name)) $missingFields[] = 'First Name';
        if (blank($user->last_name)) $missingFields[] = 'Last Name';
        if (blank($user->sex)) $missingFields[] = 'Sex';
        if (blank($user->birthdate)) $missingFields[] = 'Birthdate';
        if (blank($user->contact_number)) $missingFields[] = 'Contact Number';
        if (blank($user->email)) $missingFields[] = 'Email';
        if (blank($user->role)) $missingFields[] = 'Role';

        if (!empty($missingFields)) {
            return back()->withErrors([
                'incomplete' =>
                    'This draft cannot be approved. Please complete the following required fields first: '
                    . implode(', ', $missingFields),
            ]);
        }

        $user->update([
            'is_draft' => false,
            'is_disabled' => false,
        ]);

        return redirect()
            ->route('user-management.drafts')
            ->with('success', 'Account approved successfully.');
    }

    /**
     * Disapprove Draft Account
     */
    public function disapprove(User $user): RedirectResponse
    {
        $user->update([
            'is_draft' => false,
            'is_disabled' => true,
        ]);

        return redirect()
            ->route('user-management.drafts')
            ->with('success', 'Account disapproved successfully.');
    }

    /**
     * Draft Accounts
     */
    public function drafts(): Response
    {
        return Inertia::render('admin/UserManagement/DraftAccounts', [
            'users' => User::where('is_draft', true)
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    /**
     * Disabled Accounts
     */
    public function disabled(): Response
    {
        return Inertia::render('admin/UserManagement/DisabledAccounts', [
            'users' => User::where('is_disabled', true)
                ->where('is_draft', false)
                ->orderBy('last_name')
                ->get(),
        ]);
    }
}