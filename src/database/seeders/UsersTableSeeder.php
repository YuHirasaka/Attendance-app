<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '勤怠管理者',
            'email' => 'admin@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ];
        User::create($param);

        $param = [
            'name' => '西 怜奈',
            'email' => 'reina.n@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '山田 太郎',
            'email' => 'taro.y@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '増田 一世',
            'email' => 'issei.m@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '山本 敬吉',
            'email' => 'keikichi.y@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '秋田 朋美',
            'email' => 'tomomi.a@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '中西 教夫',
            'email' => 'norio.n@coachtech.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);
    }
}
