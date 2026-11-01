<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['email' => 'guest@example.com', 'name' => 'Guest User', 'last_name' => 'User', 'organization_name' => 'External Organization'],
            ['email' => 'guest2@example.com', 'name' => 'Guest User Two', 'last_name' => 'Two', 'organization_name' => 'Community Partner'],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrNew(['email' => $account['email']]);
            $isNew = ! $user->exists;
            $needsSecurePassword = $isNew || Hash::check('password123', (string) $user->password);

            if ($needsSecurePassword) {
                $password = 'Ua!' . bin2hex(random_bytes(16)) . 'aA';
                $account['password'] = Hash::make($password);
            }

            $user->fill([
                ...$account,
                'role' => 'guest',
                'college_id' => null,
                'college_name' => null,
            ]);
            $user->is_active = true;
            $user->email_verified_at ??= now();
            $user->save();

            if ($needsSecurePassword) {
                $this->command?->line($account['email'] . '  ' . $password);
            }
        }
    }
}
