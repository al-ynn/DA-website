<?php

namespace Database\Seeders;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoginHistorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user) {
            return;
        }

        LoginHistory::firstOrCreate(
            [
                'user_id' => $user->id,
                'login_at' => now()->subDay(),
                'successful_login' => true,
            ],
            [
                'ip_address' => '127.0.0.1',
                'browser' => 'Chrome',
                'platform' => 'Windows',
                'device' => 'Desktop',
                'logout_at' => now()->subDay()->addHour(),
            ]
        );
    }
}
