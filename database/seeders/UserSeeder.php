<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
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
        // Create a user for each store
        $user = User::create([
            'name' =>  'Super Admin',
            'email' => strtolower('dyahunter35@gmail.com'),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Attach role to user
        $user->assignRole('super_admin');
        // Create Stores

    }
}
