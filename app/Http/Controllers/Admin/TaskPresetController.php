<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskPresetRequest;
use App\Http\Requests\UpdateTaskPresetRequest;
use App\Models\TaskPreset;
use Illuminate\Http\RedirectResponse;

class TaskPresetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RedirectResponse
    {
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
        StoreTaskPresetRequest $request
    ): RedirectResponse {

        $preset = TaskPreset::create([
            'task_category_id' => $request->task_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        $preset->laboratoryTasks()->sync(
            $request->laboratory_tasks
        );

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
        TaskPreset $preset
    ): RedirectResponse {

        $preset->update([
            'task_category_id' => $request->task_category_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $preset->laboratoryTasks()->sync(
            $request->laboratory_tasks
        );

        return back()->with(
            'success',
            'Task preset updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        TaskPreset $preset
    ): RedirectResponse {

        $preset->delete();

        return back()->with(
            'success',
            'Task preset deleted successfully.'
        );
    }
}
