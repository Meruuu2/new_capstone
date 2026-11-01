{{-- resources/views/public/calendar.blade.php --}}
{{-- Used as a partial inside welcome.blade.php and portal calendars. Expects $currentMonth, $days, etc. --}}

{{-- CALENDAR SECTION (partial: no layout, no html/head/body, no @extends) --}}
@php
    $baseRoute = $calendarRoute ?? 'home';
    $today     = \Carbon\Carbon::now(); // uses app timezone (Asia/Manila)
@endphp


<div class="calendar-wrapper" id="calendar">

  <div class="cal-inner">
    @unless (!empty($hideCalendarHeading)) {{-- pages with their own banner pass hideCalendarHeading --}}
    <div class="section-head">
      <p class="ua-eyebrow">Live schedule</p>
      <h2>Reservation Schedule</h2>
      <p>Public calendar of approved and scheduled facility reservations. All times are in Philippine Standard Time.</p>
    </div>
    @endunless

    <div class="cal-wrap">
      {{-- Calendar Grid Component --}}
      <div class="cal-card">
        <div class="cal-header">
          @php
            $monthName = $currentMonth->format('F Y');
            $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
            $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
          @endphp
          <span class="cal-month">{{ $monthName }}</span>
          <div class="cal-nav-pair">

            {{-- Navigation uses dynamic route parameter --}}
            <a href="{{ route($baseRoute, ['month' => $prevMonth]) }}#calendar" class="cal-nav-btn" title="Previous month">&#8249;</a>
            <a href="{{ route($baseRoute, ['month' => $nextMonth]) }}#calendar" class="cal-nav-btn" title="Next month">&#8250;</a>

          </div>
        </div>
        <div class="cal-grid-head">
          @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div class="cal-dh">{{ $d }}</div>
          @endforeach
        </div>
        <div class="cal-days">
          @php
            $startOfMonth  = $currentMonth->copy()->startOfMonth();
            $endOfMonth    = $currentMonth->copy()->endOfMonth();
            $startOfCal    = $startOfMonth->copy()->startOfWeek(0);
            $endOfCal      = $endOfMonth->copy()->endOfWeek(6);
            $cursor        = $startOfCal->copy();
          @endphp
          @while($cursor->lte($endOfCal))
            @php
              $dateKey   = $cursor->format('Y-m-d');
              $isOther   = !$cursor->isSameMonth($currentMonth);
              $isToday   = $cursor->isSameDay($today);
              $hasEvents = isset($days[$dateKey]) && count($days[$dateKey]) > 0;
              $classes   = 'cal-day';
              if($isOther)   $classes .= ' other';
              if($isToday)   $classes .= ' today';
              if($hasEvents && !$isOther) $classes .= ' has-event';
            @endphp
            {{-- data-date / data-count let other pages (e.g. the request date picker) read which days are booked --}}
            <div class="{{ $classes }}" data-date="{{ $dateKey }}" @if($hasEvents && !$isOther) data-count="{{ count($days[$dateKey]) }}" title="{{ count($days[$dateKey]) }} reservation(s)" @endif>
              {{ $cursor->day }}
            </div>
            @php $cursor->addDay() @endphp
          @endwhile
        </div>
      </div>

      {{-- Reservations (today + upcoming combined) --}}
      <div class="res-panel" id="cal-events-sidebar">
        @php
          $isAdmin = auth()->check() && auth()->user()->isAdmin();

          // Today and upcoming reservations in one list, soonest first
          $reservations = [];
          foreach ($days as $dateStr => $bookings) {
            try {
              $dt = \Carbon\Carbon::parse($dateStr);
              if ($dt->gte($today->copy()->startOfDay())) {
                foreach ($bookings as $bk) {
                  $reservations[] = ['date' => $dt, 'booking' => $bk];
                }
              }
            } catch (\Exception $e) {}
          }
          usort($reservations, fn($a, $b) =>
            [$a['date']->timestamp, optional($a['booking']->start_time)->timestamp ?? 0]
            <=> [$b['date']->timestamp, optional($b['booking']->start_time)->timestamp ?? 0]
          );

          $typeLabels = ['admin' => 'Admin', 'college' => 'College', 'org' => 'Org Unit', 'guest' => 'Guest'];
          $typeIcons  = [
            'admin'   => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
            'college' => '<path d="M3 9l9-5 9 5-9 5-9-5z"/><path d="M7 11.2V16c0 1.1 2.2 2.5 5 2.5s5-1.4 5-2.5v-4.8"/><path d="M21 9v5"/>',
            'org'     => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.9.4 5 2.8 5 5.8"/>',
            'guest'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.5-1.5 1.7-2.3 3-2.3s2.5.8 3 2.3"/><path d="M15 10h3M15 13h3"/>',
          ];
          $fallbackIcon = '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>';
        @endphp

        <div class="res-header">
          <h3>Reservations<span class="res-count">{{ count($reservations) }}</span></h3>
          <span>Today &amp; upcoming</span>
        </div>

        <div class="res-scroll">
          @if(empty($reservations))
            <div class="res-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
              No reservations scheduled for today or the rest of this period.
            </div>
          @else
            <table class="res-table">
              <thead>
                <tr>
                  <th class="col-date" scope="col">Date</th>
                  <th scope="col">Event</th>
                  <th class="col-fac" scope="col">Facility</th>
                </tr>
              </thead>
              <tbody>
                @foreach($reservations as $item)
                  @php
                    $bk         = $item['booking'];
                    $rowIsToday = $item['date']->isSameDay($today);
                    $type       = $bk->requester_type ?? null;
                    $typeLabel  = $typeLabels[$type] ?? 'Reservation';
                    $facilities = $bk->facilities->pluck('name')->join(', ') ?: 'Facility';
                    $timeRange  = ($bk->start_time ? $bk->start_time->format('g:i A') : '')
                                . ($bk->end_time ? ' – ' . $bk->end_time->format('g:i A') : '');
                  @endphp
                  {{-- data-* feed the college request date picker / venue board. Event + requester unit are only
                       written when the including page opts in with exposeEventDetails (college calendar). --}}
                  <tr class="{{ $rowIsToday ? 'is-today' : '' }}" data-date="{{ $item['date']->toDateString() }}" data-time="{{ $timeRange }}"
                      data-start="{{ optional($bk->start_time)->format('H:i') }}" data-end="{{ optional($bk->end_time)->format('H:i') }}" data-status="{{ $bk->status }}"
                      @if (!empty($exposeEventDetails)) data-event="{{ $bk->purpose }}" data-who="{{ trim(($typeLabels[$type] ?? ucfirst((string) $type)) . ' · ' . ($bk->requester_unit ?? ''), ' ·') }}" @endif>
                    <td>
                      <div class="res-date">
                        <span class="m">{{ $item['date']->format('M') }}</span>
                        <span class="d">{{ $item['date']->format('j') }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="res-event">
                        <span class="res-logo" title="{{ $typeLabel }}">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $typeIcons[$type] ?? $fallbackIcon !!}</svg>
                        </span>
                        <div>
                          <div class="res-name">
                            @if($isAdmin)
                              {{ $bk->purpose ?? 'No purpose provided.' }}
                            @else
                              {{ $typeLabel }} Reservation
                            @endif
                            @if($rowIsToday)<span class="res-today-tag">Today</span>@endif
                          </div>
                          <div class="res-meta">
                            {{ $timeRange }}
                            @if($isAdmin)
                              · {{ optional($bk->requester)->name ?? 'Unknown' }}
                              ({{ $bk->requester_type ?? 'N/A' }} – {{ $bk->requester_unit ?? 'N/A' }})
                            @endif
                          </div>
                          <div class="res-meta res-fac-inline u-hidden">{{ $facilities }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="res-fac res-fac-cell">{{ $facilities }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
