<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTaskPresetRequest;
use App\Http\Requests\BulkAssignTaskPresetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserTaskPresetController extends Controller
{
    private function normalizeIds(array|null $values): array
    {
        return collect($values ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function syncUserTaskAssignments(User $user, array $presetIds, array $taskIds): void
    {
        $presetIds = collect($this->normalizeIds($presetIds));
        $taskIds = collect($this->normalizeIds($taskIds));

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
        User $user
    ): RedirectResponse {
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

        return back()->with(
            'success',
            'Tasks assigned successfully.'
        );
    }

    /**
     * Assign the same presets to multiple users.
     */
    public function bulkUpdate(
        BulkAssignTaskPresetRequest $request
    ): RedirectResponse {
        DB::transaction(function () use ($request): void {
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
                });
        });

        return back()->with(
            'success',
            'Tasks assigned successfully.'
        );
    }
}
