<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratoryTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
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

        foreach ($tasks as $categoryName => $taskList) {
            $category = \App\Models\TaskCategory::where('name', $categoryName)->first();

            foreach ($taskList as $task) {
                \App\Models\LaboratoryTask::firstOrCreate([
                    'task_category_id' => $category->id,
                    'name' => $task,
                ]);
            }
        }
    }
}
