<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a user for each branch
        $user = User::find(1);
        // Create branchs
        $branchs = [
            [
                'name' => 'الدبة',
                'slug' => 'al-dabbah',
            ],
            [
                'name' => 'عطبرة',
                'slug' => 'atabra',
            ],
            [
                'name' => 'امدرمان',
                'slug' => 'omdurman',
            ],
        ];

        foreach ($branchs as $branchData) {
            $branch = Branch::create([
                'name' => $branchData['name'],
                'slug' => $branchData['slug'],
            ]);

            // Attach branch to user
            $user->branch()->attach($branch->id);
        }
    }
}
