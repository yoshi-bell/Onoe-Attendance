<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emails = [
            'test1@example.com',
            'test2@example.com',
            'test3@example.com',
            'test4@example.com',
            'test5@example.com',
        ];

        foreach ($emails as $email) {
            User::factory()->create([
                'email' => $email,
                'password' => Hash::make('usertest'),
            ]);
        }

        // 管理者アカウントの作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
