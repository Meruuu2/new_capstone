<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\College;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\FormControl;
use App\Models\FormSubmission;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceTicket;
use App\Models\MaintenanceTicketMessage;
use App\Models\Notification;
use App\Models\Signatory;
use App\Models\StaffAssignment;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Policies\FacilityPolicy;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Facility::class, FacilityPolicy::class);

        foreach ([
            User::class,
            College::class,
            Facility::class,
            Equipment::class,
            Booking::class,
            MaintenanceTicket::class,
            StaffAssignment::class,
            MaintenanceLog::class,
            FormControl::class,
            FormSubmission::class,
            Signatory::class,
            MaintenanceTicketMessage::class,
            Notification::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }

        Event::listen(Login::class, function (Login $event): void {
            app(AuditLogService::class)->record('login', $event->user, newValues: [], actorId: $event->user->getKey());
        });

        Event::listen(Logout::class, function (Logout $event): void {
            app(AuditLogService::class)->record('logout', $event->user, newValues: [], actorId: $event->user->getKey());
        });

        View::composer('*', function ($view) {
            $user = auth()->user();
            $unreadNotificationsCount = 0;

            if ($user && method_exists($user, 'notifications')) {
                $unreadNotificationsCount = $user->notifications()
                    ->where('is_read', false)
                    ->count();
            }

            $view->with('unreadNotificationsCount', $unreadNotificationsCount);
        });
    }
}
  