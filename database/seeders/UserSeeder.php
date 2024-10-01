<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'lppm sinus',
            'email' => 'lppm@sinus.ac.id',
            'password' => Hash::make('rahasia'),
        ]);

        $user = User::find(1);
        $user->assignRole('superadmin');
    }
}
