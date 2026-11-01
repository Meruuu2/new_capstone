<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Alicia Mae Navarro', 'last_name' => 'Navarro', 'email' => 'navarroalicia@gsu.com', 'role' => 'admin', 'position' => 'GSU Administrator'],
            ['name' => 'Rafael Anton Villanueva', 'last_name' => 'Villanueva', 'email' => 'villanuevarafael@gsu.com', 'role' => 'admin', 'position' => 'GSU Administrator'],
            ['name' => 'Carmela Joy Delos Reyes', 'last_name' => 'Delos Reyes', 'email' => 'delosreyescarmela@gsu.com', 'role' => 'admin', 'position' => 'GSU Administrator'],
            ['name' => 'Miguel Andres Dela Cruz', 'last_name' => 'Dela Cruz', 'email' => 'delacruzmiguel@psits.com', 'role' => 'org_staff', 'position' => 'Organization Representative', 'organization_name' => 'PSITS'],
            ['name' => 'Elena Sofia Marquez', 'last_name' => 'Marquez', 'email' => 'marquezelena@cmg.com', 'role' => 'college_staff', 'position' => 'College Representative', 'college_name' => 'CMG'],
            ['name' => 'Noah Gabriel Alvarado', 'last_name' => 'Alvarado', 'email' => 'alvaradonoah@ccis.com', 'role' => 'college_staff', 'position' => 'College Representative', 'college_name' => 'CCIS'],
            ['name' => 'Isabella Mae Ledesma', 'last_name' => 'Ledesma', 'email' => 'ledesmaisabella@cea.com', 'role' => 'college_staff', 'position' => 'College Representative', 'college_name' => 'CEA'],

            ['name' => 'Patricia Mae Salazar', 'last_name' => 'Salazar', 'email' => 'adminA@example.com', 'role' => 'admin', 'position' => 'GSU Admin'],
            ['name' => 'Ramon Luis Villareal', 'last_name' => 'Villareal', 'email' => 'adminB@example.com', 'role' => 'admin', 'position' => 'GSU Admin'],
            ['name' => 'Maria Luisa Villanueva', 'last_name' => 'Villanueva', 'email' => 'college@ccis.com', 'role' => 'college_staff', 'position' => 'Dean', 'college_name' => 'CCIS'],
            ['name' => 'Adrian Miguel Cortez', 'last_name' => 'Cortez', 'email' => 'staff.ccis@example.com', 'role' => 'college_staff', 'position' => 'Program Head', 'college_name' => 'CCIS'],
            ['name' => 'Daniel Miguel Fortunato', 'last_name' => 'Fortunato', 'email' => 'college@cea.com', 'role' => 'college_staff', 'position' => 'Dean', 'college_name' => 'CEA'],
            ['name' => 'Elena Rose Delos Reyes', 'last_name' => 'Delos Reyes', 'email' => 'staff.cea@example.com', 'role' => 'college_staff', 'position' => 'Program Head', 'college_name' => 'CEA'],
            ['name' => 'Sofia Camille Mendoza', 'last_name' => 'Mendoza', 'email' => 'adminC@example.com', 'role' => 'college_staff', 'position' => 'Faculty Coordinator', 'college_name' => 'CEA'],
            ['name' => 'Andrea Nicole Flores', 'last_name' => 'Flores', 'email' => 'college@cmg.com', 'role' => 'college_staff', 'position' => 'Dean', 'college_name' => 'CMG'],
            ['name' => 'Gabriel Tomas Mendoza', 'last_name' => 'Mendoza', 'email' => 'staff.cmg@example.com', 'role' => 'college_staff', 'position' => 'Program Head', 'college_name' => 'CMG'],
            ['name' => 'Joshua Miguel Ferrer', 'last_name' => 'Ferrer', 'email' => 'provincial.ccis@example.com', 'role' => 'org_staff', 'position' => 'Organization Representative', 'organization_name' => 'Provincial CCIS'],
            ['name' => 'Samantha Joy Dela Cruz', 'last_name' => 'Dela Cruz', 'email' => 'provincial.psits@example.com', 'role' => 'org_staff', 'position' => 'Organization Representative', 'organization_name' => 'PSITS'],
            ['name' => 'Test User', 'last_name' => 'User', 'email' => 'test@example.com', 'role' => 'viewer'],
        ];

        $generatedPasswords = false;

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);
            $isNew = ! $user->exists;
            $needsSecurePassword = $isNew || Hash::check('password123', (string) $user->password);
            $collegeName = $account['college_name'] ?? null;
            $attributes = [
                ...$account,
                'college_id' => $collegeName
                    ? College::query()->where('name', $collegeName)->value('id')
                    : null,
                'college_name' => $collegeName,
                'organization_name' => $account['organization_name'] ?? null,
            ];

            if ($needsSecurePassword) {
                $password = 'Ua!' . bin2hex(random_bytes(16)) . 'aA';
                $attributes['password'] = Hash::make($password);
                $generatedPasswords = true;
            }

            $user->fill($attributes);
            $user->is_active = true;
            $user->email_verified_at ??= now();
            $user->save();

            if ($needsSecurePassword) {
                $this->command?->line($account['email'] . '  ' . $password);
            }
        }

        if ($generatedPasswords) {
            $this->command?->warn('New passwords are shown once above. Store them in a password manager.');
        }
    }
}
