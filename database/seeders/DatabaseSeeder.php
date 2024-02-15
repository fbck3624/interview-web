<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Company::factory(20)->create();

        $users = User::all();
        $companies = Company::all();

        for ($i = 0; $i < 200; $i++) {
            $user = $users->random();
            $company = $companies->random();
            Comment::factory()->create([
                'user_id' => $user,
                'company_id' => $company
            ]);
        }
    }
}
