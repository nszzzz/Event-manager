<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::query()->firstOrNew(['email' => 'user@example.com']);
        $demoUser->forceFill([
            'name' => 'Demo User',
            'password' => Hash::make('password'),
            'role' => 'user',
        ])->save();

        $helpdeskUser = User::query()->firstOrNew(['email' => 'helpdesk@example.com']);
        $helpdeskUser->forceFill([
            'name' => 'Helpdesk Agent',
            'password' => Hash::make('password'),
            'role' => 'helpdesk_agent',
        ])->save();
    }
}
