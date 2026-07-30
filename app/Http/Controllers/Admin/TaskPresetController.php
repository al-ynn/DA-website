<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskPresetRequest;
use App\Http\Requests\UpdateTaskPresetRequest;
use App\Models\TaskCategory;
use App\Models\TaskPreset;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskPresetController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', TaskPreset::class);
        return redirect()->route('task-management.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreTaskPresetRequest $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('create', TaskPreset::class);
        DB::transaction(function () use ($request, $auditLog): void {
            $preset = TaskPreset::create([
                'task_category_id' => $request->task_category_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => true,
            ]);

            $preset->laboratoryTasks()->sync($request->laboratory_tasks);

            $auditLog->record(
                action: 'preset_created',
                subject: $preset,
                newValue: $preset->fresh()->load('laboratoryTasks')->toArray(),
                request: $request,
                userId: $request->user()?->id,
            );
        });

        return back()->with(
            'success',
            'Task preset created successfully.'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateTaskPresetRequest $request,
        TaskPreset $preset,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('update', $preset);
        $old = $preset->load('laboratoryTasks')->toArray();

        DB::transaction(function () use ($request, $preset, $auditLog, $old): void {
            $preset->update([
                'task_category_id' => $request->task_category_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $preset->laboratoryTasks()->sync($request->laboratory_tasks);

            $auditLog->record(
                action: 'preset_updated',
                subject: $preset,
                oldValue: $old,
                newValue: $preset->fresh()->load('laboratoryTasks')->toArray(),
                request: $request,
                userId: $request->user()?->id,
            );
        });

        return back()->with(
            'success',
            'Task preset updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        TaskPreset $preset,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('delete', $preset);
        $old = $preset->load('laboratoryTasks')->toArray();

        DB::transaction(function () use ($preset, $auditLog, $old): void {
            $preset->laboratoryTasks()->detach();
            $preset->delete();

            $auditLog->record(
                action: 'preset_deleted',
                subject: $preset,
                oldValue: $old,
                request: request(),
                userId: request()->user()?->id,
            );
        });

        return back()->with(
            'success',
            'Task preset deleted successfully.'
        );
    }

    public function clear(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorize('create', TaskPreset::class);

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:chemist,agriculturist'],
        ]);

        $categoryName = ucfirst($validated['category']);
        $category = TaskCategory::query()
            ->where('name', $categoryName)
            ->firstOrFail();

        DB::transaction(function () use ($request, $auditLog, $category, $categoryName): void {
            $presets = TaskPreset::query()
                ->where('task_category_id', $category->id)
                ->with('laboratoryTasks')
                ->get();

            $oldValue = $presets
                ->map(fn (TaskPreset $preset) => $preset->toArray())
                ->values()
                ->all();

            TaskPreset::query()
                ->where('task_category_id', $category->id)
                ->delete();

            $auditLog->record(
                action: 'presets_cleared',
                oldValue: $oldValue,
                newValue: ['category' => $categoryName],
                request: $request,
                userId: $request->user()?->id,
            );
        });

        return back()->with(
            'success',
            "{$categoryName} presets cleared successfully."
        );
    }
}
