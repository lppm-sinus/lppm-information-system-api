<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::findOrCreate('superadmin');
        Role::findOrCreate('admin');

        $superadmin = User::create([
            'name' => 'lppm sinus',
            'email' => 'lppm@sinus.ac.id',
            'password' => Hash::make('rahasia'),
        ]);

        $admin = User::create([
            'name' => 'lppm sinus 2',
            'email' => 'lppm2@sinus.ac.id',
            'password' => Hash::make('rahasia'),
        ]);

        $superadmin->assignRole('superadmin');
        $admin->assignRole('admin');
    }
}
