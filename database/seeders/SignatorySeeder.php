<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Signatory;
use Illuminate\Database\Seeder;

class SignatorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // CCIS
            ['type' => 'dean',         'name' => 'CCIS Dean',        'unit' => 'CCIS'],
            ['type' => 'program_head', 'name' => 'CCIS Program Head','unit' => 'CCIS'],

            // CEA
            ['type' => 'dean',         'name' => 'CEA Dean',         'unit' => 'CEA'],
            ['type' => 'program_head', 'name' => 'CEA Program Head', 'unit' => 'CEA'],

            // CMG
            ['type' => 'dean',         'name' => 'CMG Dean',         'unit' => 'CMG'],
            ['type' => 'program_head', 'name' => 'CMG Program Head',  'unit' => 'CMG'],


            // Organizations
            ['type' => 'org_president','name' => 'Org A President', 'unit' => 'Org A'],
            ['type' => 'org_adviser',  'name' => 'Org A Adviser',   'unit' => 'Org A'],
            ['type' => 'org_president','name' => 'Org B President', 'unit' => 'Org B'],
            ['type' => 'org_adviser',  'name' => 'Org B Adviser',   'unit' => 'Org B'],

            // GSU head
            ['type' => 'gsu_head',     'name' => 'GSU ORG HEAD',    'unit' => 'GSU'],
        ];

        foreach ($items as $item) {
            $collegeId = null;

            if ($item['unit'] === 'CCIS' || $item['unit'] === 'CEA' || $item['unit'] === 'CMG') {
                $collegeId = College::where('name', $item['unit'])->value('id');
            }


            Signatory::updateOrCreate(
                ['type' => $item['type'], 'name' => $item['name'], 'unit' => $item['unit']],
                [
                    'is_active' => true,
                    'college_id' => $collegeId,
                ],
            );
        }
    }
}
