<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
        [
            'email' => 'admin@da-rsl.local',
        ],
        [
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'suffix' => null,

            'sex' => 'MALE',
            'birthdate' => '1990-01-01',
            'contact_number' => '+639000000000',

            'password' => Hash::make('password'),

            'role' => 'admin',
            'additional_tasks' => [
                'chemist',
                'agriculturist',
            ],

            'is_disabled' => false,
        ]
    );
    }
}