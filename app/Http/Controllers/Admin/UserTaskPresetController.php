<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTaskPresetRequest;
use App\Http\Requests\BulkAssignTaskPresetRequest;
use App\Models\LaboratoryTask;
use App\Services\AuditLogService;
use App\Models\TaskPreset;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UserTaskPresetController extends Controller
{
    use AuthorizesRequests;

    private function normalizeIds(array|null $values): array
    {
        return collect($values ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getAllowedCategoriesForUser(User $user): array
    {
        if (strtolower((string) $user->role) === 'admin') {
            return ['admin', 'chemist', 'agriculturist'];
        }

        $categories = collect([$user->role])
            ->merge($user->additional_tasks ?? [])
            ->map(fn ($value) => strtolower((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_values(array_filter($categories, fn ($category) => in_array($category, ['admin', 'chemist', 'agriculturist'], true)));
    }

    private function validateAssignments(User $user, array $presetIds, array $taskIds): void
    {
        $allowedCategories = $this->getAllowedCategoriesForUser($user);

        $presets = TaskPreset::query()
            ->with('category')
            ->whereIn('id', $presetIds)
            ->get()
            ->keyBy('id');

        $tasks = LaboratoryTask::query()
            ->with('category')
            ->whereIn('id', $taskIds)
            ->get()
            ->keyBy('id');

        if ($presets->count() !== count($presetIds)) {
            throw ValidationException::withMessages([
                'task_presets' => 'One or more selected presets are invalid.',
            ]);
        }

        if ($tasks->count() !== count($taskIds)) {
            throw ValidationException::withMessages([
                'task_ids' => 'One or more selected tasks are invalid.',
            ]);
        }

        foreach ($presets as $preset) {
            $category = strtolower($preset->category?->name ?? '');
            if (! in_array($category, $allowedCategories, true)) {
                throw ValidationException::withMessages([
                    'task_presets' => 'One or more selected presets do not match the user role.',
                ]);
            }
        }

        foreach ($tasks as $task) {
            $category = strtolower($task->category?->name ?? '');
            if (! in_array($category, $allowedCategories, true)) {
                throw ValidationException::withMessages([
                    'task_ids' => 'One or more selected tasks do not match the user role.',
                ]);
            }
        }
    }

    private function syncUserTaskAssignments(User $user, array $presetIds, array $taskIds): void
    {
        $presetIds = collect($this->normalizeIds($presetIds));
        $taskIds = collect($this->normalizeIds($taskIds));

        $this->validateAssignments($user, $presetIds->all(), $taskIds->all());

        if (Schema::hasTable('user_task_presets')) {
            DB::table('user_task_presets')
                ->where('user_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('task_user')) {
            DB::table('task_user')
                ->where('user_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('user_task_assignments')) {
            DB::table('user_task_assignments')
                ->where('user_id', $user->id)
                ->delete();
        }

        $now = now();

        if ($presetIds->isNotEmpty()) {
            $presetRows = $presetIds->map(fn (int $presetId) => [
                'user_id' => $user->id,
                'task_preset_id' => $presetId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if (Schema::hasTable('user_task_presets')) {
                DB::table('user_task_presets')->insert($presetRows);
            }

            if (Schema::hasTable('task_user')) {
                DB::table('task_user')->insert($presetRows);
            }
        }

        if ($taskIds->isNotEmpty()) {
            $taskRows = $taskIds->map(fn (int $taskId) => [
                'user_id' => $user->id,
                'laboratory_task_id' => $taskId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if (Schema::hasTable('user_task_assignments')) {
                DB::table('user_task_assignments')->insert($taskRows);
            }
        }
    }

    /**
     * Assign presets to a user.
     */
    public function update(
        AssignTaskPresetRequest $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('update', $user);
        $old = [
            'task_presets' => $user->taskPresets()->pluck('task_presets.id')->all(),
            'assigned_tasks' => $user->assignedTasks()->pluck('laboratory_tasks.id')->all(),
        ];

        $presetIds = $this->normalizeIds(
            $request->input(
                'task_presets',
                $request->input('presetIds', $request->input('selectedPresets')),
            ),
        );

        $taskIds = $this->normalizeIds(
            $request->input(
                'task_ids',
                $request->input('taskIds', $request->input('selectedTasks')),
            ),
        );

        DB::transaction(function () use ($user, $presetIds, $taskIds, $request, $auditLog, $old): void {
            $this->syncUserTaskAssignments(
                $user,
                $presetIds,
                $taskIds,
            );

            $auditLog->record(
                action: 'task_assignment_updated',
                subject: $user,
                oldValue: $old,
                newValue: [
                    'task_presets' => $presetIds,
                    'assigned_tasks' => $taskIds,
                ],
                request: $request,
                userId: $user->id,
            );
        });

        return back()->with(
            'success',
            'Tasks assigned successfully.'
        );
    }

    /**
     * Assign the same presets to multiple users.
     */
    public function bulkUpdate(
        BulkAssignTaskPresetRequest $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('update', new User());
        DB::transaction(function () use ($request, $auditLog): void {
            User::query()
                ->whereIn('id', $request->user_ids)
                ->get()
                ->each(function (User $user) use ($request): void {
                    $presetIds = $this->normalizeIds(
                        $request->input(
                            'task_presets',
                            $request->input('presetIds', $request->input('selectedPresets')),
                        ),
                    );

                    $taskIds = $this->normalizeIds(
                        $request->input(
                            'task_ids',
                            $request->input('taskIds', $request->input('selectedTasks')),
                        ),
                    );

                    $this->syncUserTaskAssignments(
                        $user,
                        $presetIds,
                        $taskIds,
                    );

                    app(AuditLogService::class)->record(
                        action: 'bulk_task_assignment_updated',
                        subject: $user,
                        oldValue: null,
                        newValue: [
                            'task_presets' => $presetIds,
                            'assigned_tasks' => $taskIds,
                        ],
                        request: $request,
                        userId: $user->id,
                    );
                });
        });

        return back()->with(
            'success',
            'Tasks assigned successfully.'
        );
    }
}
