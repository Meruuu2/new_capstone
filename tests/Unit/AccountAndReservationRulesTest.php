<?php

namespace Tests\Unit;

use App\Rules\OperatingHours;
use App\Rules\PersonName;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class AccountAndReservationRulesTest extends TestCase
{
    public function test_person_names_allow_letters_spaces_hyphens_and_apostrophes_only(): void
    {
        foreach (['Anne-Marie O’Neil', "D'Arcy Smith", 'Éloïse Cruz'] as $name) {
            $this->assertTrue(
                Validator::make(['name' => $name], ['name' => [new PersonName]])->passes(),
                "Expected {$name} to be accepted."
            );
        }

        foreach (['Alex2', 'Name_One', ' Alex'] as $name) {
            $this->assertTrue(
                Validator::make(['name' => $name], ['name' => [new PersonName]])->fails(),
                "Expected {$name} to be rejected."
            );
        }
    }

    public function test_reservation_hours_include_six_am_and_seven_pm_but_reject_outside_times(): void
    {
        foreach (['06:00', '19:00'] as $time) {
            $this->assertTrue(
                Validator::make(['time' => $time], ['time' => [new OperatingHours]])->passes()
            );
        }

        foreach (['05:59', '19:01'] as $time) {
            $this->assertTrue(
                Validator::make(['time' => $time], ['time' => [new OperatingHours]])->fails()
            );
        }
    }

    public function test_password_rule_accepts_a_94_character_password_without_requiring_a_digit(): void
    {
        $rules = ['password' => [Password::min(8)->max(94)->mixedCase()->symbols()]];
        $password = str_repeat('a', 92) . 'A!';

        $this->assertSame(94, strlen($password));
        $this->assertTrue(Validator::make(['password' => $password], $rules)->passes());
        $this->assertTrue(Validator::make(['password' => 'Abcdefgh'], $rules)->fails());
        $this->assertTrue(Validator::make(['password' => 'abcdefgh!'], $rules)->fails());
        $this->assertTrue(Validator::make(['password' => $password . 'a'], $rules)->fails());
    }
}
