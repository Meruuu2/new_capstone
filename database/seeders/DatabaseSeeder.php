<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // These base records are required by the staff accounts and reservation forms.
        $this->call(CollegeSeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(SignatorySeeder::class);
        $this->call(SampleUsersSeeder::class);
        $this->call(GuestUserSeeder::class);
    }
}
