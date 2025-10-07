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
            ['name' => '鈴木　一朗', 'email' => 'ichiro.s@coachtech.com', 'role' => 'admin'],
            ['name' => '山田　太郎', 'email' => 'taro.y@coachtech.com', 'role' => 'user'],
            ['name' => '西　伶奈', 'email' => 'reina.n@coachtech.com', 'role' => 'user'],
            ['name' => '増田　一世', 'email' => 'issei.m@coachtech.com', 'role' => 'user'],
            ['name' => '山本　敬吉', 'email' => 'keikichi.y@coachtech.com', 'role' => 'user'],
            ['name' => '秋田　朋美', 'email' => 'tomomi.a@coachtech.com', 'role' => 'user'],
            ['name' => '中西　教夫', 'email' => 'norio.n@coachtech.com', 'role' => 'user'],
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
