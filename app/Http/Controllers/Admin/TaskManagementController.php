<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTask;
use App\Models\TaskCategory;
use App\Models\TaskPreset;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class TaskManagementController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', TaskPreset::class);
        $this->ensureTaskManagementSeedData();

        $taskCategories = TaskCategory::query()
            ->with([
                'laboratoryTasks' => fn ($query) => $query->orderBy('name'),
                'taskPresets' => fn ($query) => $query
                    ->with(['laboratoryTasks' => fn ($taskQuery) => $taskQuery->orderBy('name')])
                    ->orderBy('name'),
            ])
            ->orderByRaw("CASE name WHEN 'Admin' THEN 0 WHEN 'Chemist' THEN 1 WHEN 'Agriculturist' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get()
            ->map(function (TaskCategory $category) {
                $key = strtolower($category->name);
                $allowPresets = $key !== 'admin';

                return [
                    'id' => $category->id,
                    'key' => $key,
                    'name' => $category->name,
                    'title' => $category->name,
                    'laboratory_tasks' => $category->laboratoryTasks->map(function (LaboratoryTask $task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->name,
                            'task_category_id' => $task->task_category_id,
                        ];
                    })->values(),
                    'presets' => $allowPresets
                        ? $category->taskPresets->map(function (TaskPreset $preset) use ($key) {
                            return [
                                'id' => $preset->id,
                                'name' => $preset->name,
                                'description' => $preset->description,
                                'category' => $key,
                                'task_category_id' => $preset->task_category_id,
                                'tasks' => $preset->laboratoryTasks->pluck('name')->values(),
                                'task_ids' => $preset->laboratoryTasks->pluck('id')->values(),
                            ];
                        })->values()
                        : collect([]),
                ];
            })
            ->values();

        $allPresets = $taskCategories
            ->pluck('presets')
            ->flatten(1)
            ->values();

        $users = User::query()
            ->where('is_disabled', false)
            ->where('is_draft', false)
            ->with([
                'taskPresets.category',
                'taskPresets.laboratoryTasks' => fn ($query) => $query->orderBy('name'),
                'legacyTaskPresets.category',
                'legacyTaskPresets.laboratoryTasks' => fn ($query) => $query->orderBy('name'),
                'assignedTasks.category',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,

                    'surname' => $user->last_name,
                    'givenname' => $user->first_name,
                    'middlename' => $user->middle_name,
                    'suffix' => $user->suffix,

                    'status' => 'ACTIVE',

                    'category' => strtoupper($user->role),

                    'tasks' => $user->additional_tasks ?? [],
                    'additionalCategories' => $user->additional_tasks ?? [],

                    'assigned_presets' => $user->taskPresets
                        ->concat($user->legacyTaskPresets)
                        ->unique('id')
                        ->values()
                        ->map(function ($preset) {
                            return [
                                'id' => $preset->id,
                                'name' => $preset->name,
                                'description' => $preset->description,
                                'category' => $preset->category?->name ? strtolower($preset->category->name) : null,
                                'task_category_id' => $preset->task_category_id,
                                'tasks' => $preset->laboratoryTasks->pluck('name')->values(),
                                'task_ids' => $preset->laboratoryTasks->pluck('id')->values(),
                            ];
                        })->values(),

                    'assigned_tasks' => $user->assignedTasks->map(function (LaboratoryTask $task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->name,
                            'category' => $task->category?->name ? strtolower($task->category->name) : null,
                            'task_category_id' => $task->task_category_id,
                        ];
                    })->values(),
                ];
            });

        return Inertia::render('admin/TaskManagement/index', [
            'users' => $users,
            'taskCategories' => $taskCategories,
            'presets' => $allPresets,
        ]);
    }

    private function ensureTaskManagementSeedData(): void
    {
        $seedData = [
            'Admin' => [
                'Reviewer',
                'Certifier',
                'Noter',
            ],
            'Chemist' => [
                'pH',
                'EC Analysis',
                'Organic Matter Analysis',
                'Available Phosphorus',
                'Potassium',
                'Calcium',
                'Magnesium',
                'Sodium',
                'Zinc',
                'Copper',
                'Iron',
                'Manganese',
            ],
            'Agriculturist' => [
                'Soil Moisture',
                'Particle Size Analysis',
                'Soil Texture',
                'Fertilizer Recommendation',
            ],
        ];

        foreach ($seedData as $categoryName => $tasks) {
            $category = TaskCategory::firstOrCreate([
                'name' => $categoryName,
            ]);

            foreach ($tasks as $taskName) {
                LaboratoryTask::firstOrCreate([
                    'task_category_id' => $category->id,
                    'name' => $taskName,
                ]);
            }
        }
    }

}
