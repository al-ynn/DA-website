<?php

namespace Database\Seeders;

use App\Models\TaskCategory;
use Illuminate\Database\Seeder;

class TaskCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'Admin',
            'Chemist',
            'Agriculturist',
        ] as $category) {

            TaskCategory::firstOrCreate([
                'name' => $category,
            ]);
        }
    }
}
