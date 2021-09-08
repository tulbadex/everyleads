<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        //
        DB::table('users')->insert([
            'name' => 'Ibrahim',
            'email' => 'tulbadex@gmail.com',
            'password' => 'password'
        ]);

        /* DB::table('users')->insert([
            'fname' => 'Ibrahim',
            'lname' => 'Adedayo',
            'username' => 'tulbadex',
            'email' => 'tulbadex@gmail.com',
            'password' => 'password',
            'phone' => '09051623555'
        ]); */
    }
}
