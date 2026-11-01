<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the facilities table with the official UA facilities list.
     *
     * GSU owns and controls non-AVR facilities.
     * Individual college AVRs are owned and controlled by their respective colleges.
     */
    public function run(): void
    {
        $facilities = [
            // GSU-controlled, non-AVR facilities
            [
'name'          => 'BUSALIAN HALL',
                'location'      => 'Main Campus',
                'owner_type'    => 'gsu',
                'owner_college' => null,
'description'   => 'BUSALIAN HALL',
            ],
            [
                'name'          => 'PAGHIUSA HALL',
                'location'      => 'Main Campus',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'PAGHIUSA HALL',
            ],

            [
                'name'          => 'E-HUB',
                'location'      => 'Main Campus',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'E-HUB',
            ],
            [
                'name'          => 'BALAY NI JUAN',
                'location'      => 'Main Campus',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'BALAY NI JUAN',
            ],
            [
                'name'          => 'GRAND STAND',
                'location'      => 'Sports Complex',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'GRAND STAND',
            ],
            [
                'name'          => 'COVERED GYM',
                'location'      => 'Sports Complex',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'COVERED GYM',
            ],
            [
                'name'          => 'TRACK OVAL',
                'location'      => 'Sports Complex',
                'owner_type'    => 'gsu',
                'owner_college' => null,
                'description'   => 'TRACK OVAL',
            ],

            // College-owned AVRs (college controls their availability)
            [
                'name'          => 'ICT AVR',
                'location'      => 'ICT Building',
                'owner_type'    => 'college',
                'owner_college' => 'CCIS',
                'description'   => 'ICT AVR',
            ],
            [
                'name'          => 'CEA AVR',
                'location'      => 'CEA Building',
                'owner_type'    => 'college',
                'owner_college' => 'CEA',
                'description'   => 'CEA AVR',
            ],
            [
                'name'          => 'CBA AVR',
                'location'      => 'CBA Building',
                'owner_type'    => 'college',
                'owner_college' => 'CMG',
                'description'   => 'CBA AVR',
            ],

            [
                'name'          => 'NEW AVR',
                'location'      => 'Main Campus',
                'owner_type'    => 'gsu', // change to 'college' + owner_college if NEW AVR is college-controlled
                'owner_college' => null,
                'description'   => 'NEW AVR',
            ],

            // "OTHERS" is not inserted as a row; it's represented as a free-text
            // field in the request form when the listed facilities do not apply.
        ];

        foreach ($facilities as $data) {
            $collegeId = null;

            if (!empty($data['owner_college'])) {
                $collegeId = College::where('name', $data['owner_college'])->value('id');
            }

            Facility::updateOrCreate(
                [
                    'name'          => $data['name'],
                    'owner_type'    => $data['owner_type'],
                    'owner_college' => $data['owner_college'],
                ],
                [
                    'location'    => $data['location'],
                    'description' => $data['description'],
                    'is_active'   => true,
                    'college_id'  => $collegeId,
                ]
            );
        }
    }
}