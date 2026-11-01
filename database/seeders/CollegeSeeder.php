<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $colleges = [
            ['name' => 'CCIS', 'short_name' => 'CCIS', 'description' => 'CCIS for seeded test data.'],
            ['name' => 'CEA',  'short_name' => 'CEA',  'description' => 'CEA for seeded test data.'],
            ['name' => 'CMG',  'short_name' => 'CMG',  'description' => 'CMG for seeded test data.'],
        ];

        foreach ($colleges as $data) {
            College::updateOrCreate(
                ['name' => $data['name']],
                [
                    'short_name' => $data['short_name'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
