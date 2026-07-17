<?php

namespace Database\Seeders;

use App\Models\DefaultTaskPreset;
use App\Models\TaskCategory;
use App\Models\LaboratoryTask;
use Illuminate\Database\Seeder;

class DefaultTaskPresetSeeder extends Seeder
{
    public function run(): void
    {
        DefaultTaskPreset::truncate();

        $chemist = TaskCategory::where('name', 'Chemist')->first();
        $agri = TaskCategory::where('name', 'Agriculturist')->first();

        DefaultTaskPreset::create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist #1',
            'description' => 'pH and EC Analysis',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','pH')->value('id'),
                LaboratoryTask::where('name','EC Analysis')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist #2',
            'description' => 'Organic Matter Analysis',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Organic Matter Analysis')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist #3',
            'description' => 'Available Phosphorus',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Available Phosphorus')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist #4',
            'description' => 'Exchangeable Bases',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Potassium')->value('id'),
                LaboratoryTask::where('name','Calcium')->value('id'),
                LaboratoryTask::where('name','Magnesium')->value('id'),
                LaboratoryTask::where('name','Sodium')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist #5',
            'description' => 'Micronutrients',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Zinc')->value('id'),
                LaboratoryTask::where('name','Copper')->value('id'),
                LaboratoryTask::where('name','Iron')->value('id'),
                LaboratoryTask::where('name','Manganese')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $agri->id,
            'name' => 'Agriculturist #1',
            'description' => 'Moisture',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Soil Moisture')->value('id'),
            ],
        ]);

        DefaultTaskPreset::create([
            'task_category_id' => $agri->id,
            'name' => 'Agriculturist #2',
            'description' => 'Particle Size Analysis',
            'laboratory_task_ids' => [
                LaboratoryTask::where('name','Particle Size Analysis')->value('id'),
                LaboratoryTask::where('name','Soil Texture')->value('id'),
            ],
        ]);
    }
}