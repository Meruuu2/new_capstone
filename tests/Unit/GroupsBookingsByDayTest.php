<?php

namespace Tests\Unit;

use App\Http\Controllers\Traits\GroupsBookingsByDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class GroupsBookingsByDayTest extends TestCase
{
    public function test_invalid_month_query_falls_back_to_the_current_month(): void
    {
        Carbon::setTestNow('2026-10-24 12:00:00');
        $resolver = new class {
            use GroupsBookingsByDay;

            public function month(Request $request): Carbon
            {
                return $this->resolveMonth($request);
            }
        };

        try {
            foreach (['abc', '99999-01', '2026-13'] as $invalidMonth) {
                $this->assertSame(
                    '2026-10',
                    $resolver->month(Request::create('/calendar?month=' . $invalidMonth))->format('Y-m')
                );
            }
            $this->assertSame(
                '2026-11',
                $resolver->month(Request::create('/calendar?month=2026-11'))->format('Y-m')
            );
        } finally {
            Carbon::setTestNow();
        }
    }
}
