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
            'name' => 'スタッフ',
            'email' => 'general@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ];
        User::create($param);

        $param = [
            'name' => '勤怠管理者',
            'email' => 'admin@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ];
        User::create($param);
    }
}
