<?php

namespace Tests\Feature;

use App\Models\LaboratoryTask;
use App\Models\TaskCategory;
use App\Models\TaskPreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPresetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_preset_saves_exact_selected_tasks_and_rejects_other_categories(): void
    {
        $admin = $this->createAdmin();
        [$chemist, $agriculturist] = $this->createTaskCatalog();

        $ph = $chemist->laboratoryTasks()->where('name', 'pH')->firstOrFail();
        $potassium = $chemist->laboratoryTasks()->where('name', 'Potassium')->firstOrFail();
        $soilMoisture = $agriculturist->laboratoryTasks()
            ->where('name', 'Soil Moisture')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('task-management.presets.store'), [
                'task_category_id' => $chemist->id,
                'name' => 'Selected Chemist Tasks',
                'description' => 'Only the checked tasks',
                'laboratory_tasks' => [$ph->id, $potassium->id],
            ])
            ->assertRedirect();

        $preset = TaskPreset::query()
            ->where('name', 'Selected Chemist Tasks')
            ->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$ph->id, $potassium->id],
            $preset->laboratoryTasks()->pluck('laboratory_tasks.id')->all()
        );

        $this->actingAs($admin)
            ->from(route('task-management.index'))
            ->post(route('task-management.presets.store'), [
                'task_category_id' => $chemist->id,
                'name' => 'Invalid Mixed Preset',
                'description' => '',
                'laboratory_tasks' => [$ph->id, $soilMoisture->id],
            ])
            ->assertRedirect(route('task-management.index'))
            ->assertSessionHasErrors('laboratory_tasks.1');

        $this->assertDatabaseMissing('task_presets', [
            'name' => 'Invalid Mixed Preset',
        ]);
    }

    public function test_clear_removes_only_the_requested_category_presets(): void
    {
        $admin = $this->createAdmin();
        [$chemist, $agriculturist] = $this->createTaskCatalog();

        TaskPreset::query()->create([
            'task_category_id' => $chemist->id,
            'name' => 'Custom Chemist',
            'description' => null,
            'is_active' => true,
        ]);

        TaskPreset::query()->create([
            'task_category_id' => $agriculturist->id,
            'name' => 'Custom Agriculturist',
            'description' => null,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('task-management.presets.clear'), [
                'category' => 'chemist',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('task_presets', [
            'task_category_id' => $chemist->id,
            'name' => 'Custom Chemist',
        ]);
        $this->assertDatabaseHas('task_presets', [
            'task_category_id' => $agriculturist->id,
            'name' => 'Custom Agriculturist',
        ]);
        $this->assertSame(
            0,
            TaskPreset::query()->where('task_category_id', $chemist->id)->count()
        );
    }

    public function test_admin_can_save_presets_and_individual_tasks_for_a_user(): void
    {
        $admin = $this->createAdmin();
        [$chemist, $agriculturist] = $this->createTaskCatalog();

        $ph = $chemist->laboratoryTasks()->where('name', 'pH')->firstOrFail();
        $ec = $chemist->laboratoryTasks()->where('name', 'EC Analysis')->firstOrFail();

        $preset = TaskPreset::query()->create([
            'task_category_id' => $chemist->id,
            'name' => 'Chemist Preset',
            'description' => null,
            'is_active' => true,
        ]);
        $preset->laboratoryTasks()->attach($ph->id);

        $soilMoisture = $agriculturist->laboratoryTasks()
            ->where('name', 'Soil Moisture')
            ->firstOrFail();
        $agriculturistPreset = TaskPreset::query()->create([
            'task_category_id' => $agriculturist->id,
            'name' => 'Agriculturist Preset',
            'description' => null,
            'is_active' => true,
        ]);
        $agriculturistPreset->laboratoryTasks()->attach($soilMoisture->id);

        $chemistUser = User::query()->create([
            'first_name' => 'Assigned',
            'last_name' => 'Chemist',
            'email' => 'chemist@example.test',
            'password' => 'password',
            'role' => 'chemist',
            'is_draft' => false,
            'is_disabled' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('task-management.users.task-presets.update', $chemistUser), [
                'task_presets' => [$preset->id],
                'task_ids' => [$ec->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_task_presets', [
            'user_id' => $chemistUser->id,
            'task_preset_id' => $preset->id,
        ]);
        $this->assertDatabaseHas('user_task_assignments', [
            'user_id' => $chemistUser->id,
            'laboratory_task_id' => $ec->id,
        ]);

        $secondChemist = User::query()->create([
            'first_name' => 'Second',
            'last_name' => 'Chemist',
            'email' => 'second.chemist@example.test',
            'password' => 'password',
            'role' => 'chemist',
            'is_draft' => false,
            'is_disabled' => false,
        ]);
        $secondChemist->taskPresets()->attach($preset->id);
        $secondChemist->assignedTasks()->attach($ec->id);

        $this->actingAs($admin)
            ->put(route('task-management.users.task-presets.bulk-update'), [
                'user_ids' => [$chemistUser->id, $secondChemist->id],
                'task_presets' => [],
                'task_ids' => [],
            ])
            ->assertRedirect();

        foreach ([$chemistUser, $secondChemist] as $clearedUser) {
            $this->assertDatabaseMissing('user_task_presets', [
                'user_id' => $clearedUser->id,
            ]);
            $this->assertDatabaseMissing('user_task_assignments', [
                'user_id' => $clearedUser->id,
            ]);
        }

        $this->actingAs($admin)
            ->put(route('task-management.users.task-presets.update', $admin), [
                'task_presets' => [$preset->id, $agriculturistPreset->id],
                'task_ids' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_task_presets', [
            'user_id' => $admin->id,
            'task_preset_id' => $preset->id,
        ]);
        $this->assertDatabaseHas('user_task_presets', [
            'user_id' => $admin->id,
            'task_preset_id' => $agriculturistPreset->id,
        ]);
    }

    private function createAdmin(): User
    {
        return User::query()->create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'is_draft' => false,
            'is_disabled' => false,
        ]);
    }

    /**
     * @return array{TaskCategory, TaskCategory}
     */
    private function createTaskCatalog(): array
    {
        $catalog = [
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

        $categories = [];

        foreach ($catalog as $categoryName => $taskNames) {
            $category = TaskCategory::query()->create(['name' => $categoryName]);

            foreach ($taskNames as $taskName) {
                LaboratoryTask::query()->create([
                    'task_category_id' => $category->id,
                    'name' => $taskName,
                ]);
            }

            $categories[] = $category;
        }

        return $categories;
    }
}
