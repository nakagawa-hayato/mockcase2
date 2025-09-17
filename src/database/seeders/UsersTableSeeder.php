<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => '鈴木一朗', 'email' => 'ichiro.s@coachtech.com', 'role' => 'admin'],
            ['name' => '山田太郎', 'email' => 'taro.y@coachtech.com', 'role' => 'user'],
            ['name' => '西伶奈', 'email' => 'reina.n@coachtech.com', 'role' => 'user'],
            ['name' => '増田一世', 'email' => 'issei.m@coachtech.com', 'role' => 'user'],
            ['name' => '山本敬吉', 'email' => 'keikichi.y@coachtech.com', 'role' => 'user'],
            ['name' => '秋田朋美', 'email' => 'tomomi.a@coachtech.com', 'role' => 'user'],
            ['name' => '中西教夫', 'email' => 'norio.n@coachtech.com', 'role' => 'user'],
        ];

        foreach ($users as $u) {
            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password123'),
                'role' => $u['role'],
            ]);
        }
    }
}
