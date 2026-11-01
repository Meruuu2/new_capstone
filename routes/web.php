<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\MonthlyRecordsController as AdminMonthlyRecordsController;
use App\Http\Controllers\College\DashboardController as CollegeDashboardController;
use App\Http\Controllers\College\FacilityController as CollegeFacilityController;
use App\Http\Controllers\College\FormController as CollegeFormController;
use App\Http\Controllers\Org\DashboardController as OrgDashboardController;
use App\Http\Controllers\Org\FormController as OrgFormController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GsuFormController;
use App\Http\Controllers\PublicCalendarController;

// Authentication routes (simple web login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['GET','POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Notifications (for all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
});

// home route for viewer/general
Route::get('/', [\App\Http\Controllers\PublicCalendarController::class, 'index'])
    ->name('home');
Route::get('/calendar/data', [\App\Http\Controllers\PublicCalendarController::class, 'calendarData'])
    ->middleware('throttle:120,1')
    ->name('calendar.data');

// Landing page "Track a request": look up a facility ticket by its FM code + last name (read-only, rate-limited)
Route::get('/track/ticket', [\App\Http\Controllers\TicketLookupController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('ticket.lookup');

// Public facility request for guests (outsiders): no account, follow up with the ticket code
Route::get('/request-facility', [\App\Http\Controllers\PublicFacilityRequestController::class, 'create'])
    ->name('public.request.create');
Route::post('/request-facility', [\App\Http\Controllers\PublicFacilityRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.request.store');
Route::get('/request-facility/submitted/{submission}', [\App\Http\Controllers\PublicFacilityRequestController::class, 'submitted'])
    ->name('public.request.submitted');
Route::get('/forms/facilities/{submission}/payment-receipt', [\App\Http\Controllers\PaymentReceiptController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('payment-receipts.show');
Route::get('/forms/facilities/{submission}/attachments/{index}', [\App\Http\Controllers\PaymentReceiptController::class, 'attachment'])
    ->whereNumber('index')
    ->middleware('throttle:30,1')
    ->name('payment-attachments.show');

// Guest PDF Generation (only accessible after approval via tracking code)
Route::get('/guest/forms/facilities/{submission}/pdf', [\App\Http\Controllers\Guest\FacilitiesFormPdfController::class, 'generate'])
    ->middleware(['signed:relative', 'throttle:10,1'])
    ->name('guest.forms.facilities.pdf');
Route::post('/track/ticket/{submission}/payment', [\App\Http\Controllers\TicketLookupController::class, 'uploadPayment'])
    ->middleware(['signed:relative', 'throttle:5,1'])
    ->name('ticket.payment.upload');

Route::view('/venue-rates', 'public.rates')->name('public.rates');
Route::view('/guest-access', 'public.guest-access')->name('public.guest');





// Simple health check (for debugging)
Route::get('/healthz', function () {
    return response()->json([
        'status' => 'ok',
        'time'   => now()->toDateTimeString(),
    ]);
});

// Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');

    Route::get('/admin/maintenance-tickets', [\App\Http\Controllers\Admin\MaintenanceTicketController::class, 'index'])
        ->name('admin.maintenance-tickets.index');
    Route::get('/admin/maintenance-tickets/{ticket}', [\App\Http\Controllers\Admin\MaintenanceTicketController::class, 'show'])
        ->name('admin.maintenance-tickets.show');
    Route::put('/admin/maintenance-tickets/{ticket}', [\App\Http\Controllers\Admin\MaintenanceTicketController::class, 'update'])
        ->name('admin.maintenance-tickets.update');
    // Conversation with the college staff who sent the ticket (the page polls the GET route)
    Route::get('/admin/maintenance-tickets/{ticket}/messages', [\App\Http\Controllers\Admin\MaintenanceTicketController::class, 'messages'])
        ->name('admin.maintenance-tickets.messages');
    Route::post('/admin/maintenance-tickets/{ticket}/messages', [\App\Http\Controllers\Admin\MaintenanceTicketController::class, 'sendMessage'])
        ->middleware('throttle:30,1')
        ->name('admin.maintenance-tickets.messages.store');

    // Facilities
    Route::get('/admin/facilities', [AdminFacilityController::class, 'index'])->name('admin.facilities.index');
    Route::get('/admin/facilities/create', [AdminFacilityController::class, 'create'])->name('admin.facilities.create');
    Route::post('/admin/facilities', [AdminFacilityController::class, 'store'])->name('admin.facilities.store');
    Route::get('/admin/facilities/{facility}/edit', [AdminFacilityController::class, 'edit'])->name('admin.facilities.edit');
    Route::put('/admin/facilities/{facility}', [AdminFacilityController::class, 'update'])->name('admin.facilities.update');
    Route::patch('/admin/facilities/{facility}/deactivate', [AdminFacilityController::class, 'deactivate'])->name('admin.facilities.deactivate');
    Route::patch('/admin/facilities/{facility}/activate', [AdminFacilityController::class, 'activate'])->name('admin.facilities.activate');

    // GSU Forms (accessible by admin/GSU staff)
    Route::get('/forms/facilities', [GsuFormController::class, 'showFacilities'])->name('forms.facilities.show');
    Route::post('/forms/facilities/download', [GsuFormController::class, 'downloadFacilities'])->name('forms.facilities.download');
    Route::get('/forms/repair', [GsuFormController::class, 'showRepair'])->name('forms.repair.show');
    Route::post('/forms/repair/download', [GsuFormController::class, 'downloadRepair'])->name('forms.repair.download');

    // Facilities form submissions (GSU review)
    Route::get('/admin/forms/facilities', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'index'])
        ->name('admin.forms.facilities.index');
    Route::get('/admin/forms/facilities/{submission}', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'show'])
        ->name('admin.forms.facilities.show');
    Route::post('/admin/forms/facilities/{submission}/approve', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'approve'])
        ->name('admin.forms.facilities.approve');
    Route::post('/admin/forms/facilities/{submission}/disapprove', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'disapprove'])
        ->name('admin.forms.facilities.disapprove');
    Route::post('/admin/forms/facilities/{submission}/verify-payment', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'verifyPayment'])
        ->name('admin.forms.facilities.verify-payment');
    Route::post('/admin/forms/facilities/{submission}/set-reservation', [\App\Http\Controllers\Admin\FormSubmissionController::class, 'setReservation'])
        ->name('admin.forms.facilities.set-reservation');

    // Generate PDF for approved facilities requests
    Route::get(
        '/admin/forms/facilities/{submission}/pdf',
        [\App\Http\Controllers\Admin\FacilitiesFormPdfController::class, 'generate']
    )->name('admin.forms.facilities.pdf');

    // User management
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserController::class, 'deactivate'])->name('admin.users.deactivate');

    // Monthly Records
    Route::get('/admin/monthly-records', [AdminMonthlyRecordsController::class, 'index'])->name('admin.monthly-records.index');
    Route::get('/admin/monthly-records/export', [AdminMonthlyRecordsController::class, 'exportCharts'])->name('admin.monthly-records.export');

    // Direct booking (GSU high-priority)
    Route::get('/admin/bookings/create-direct', [\App\Http\Controllers\Admin\BookingController::class, 'createDirect'])
        ->name('admin.bookings.create-direct');
    Route::post('/admin/bookings/store-direct', [\App\Http\Controllers\Admin\BookingController::class, 'storeDirect'])
        ->name('admin.bookings.store-direct');


    // Booking management (GSU can override & reschedule)
    Route::get('/admin/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])
        ->name('admin.bookings.index');
    Route::get('/admin/bookings/{booking}/edit', [\App\Http\Controllers\Admin\BookingController::class, 'edit'])
        ->name('admin.bookings.edit');
    Route::put('/admin/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'update'])
        ->name('admin.bookings.update');
    Route::post('/admin/bookings/{booking}/cancel', [\App\Http\Controllers\Admin\BookingController::class, 'cancel'])
        ->name('admin.bookings.cancel');
    Route::get('/admin/calendar', [\App\Http\Controllers\Admin\BookingController::class, 'calendar'])
        ->name('admin.calendar');
    Route::get('/admin/overview', [\App\Http\Controllers\Admin\BookingController::class, 'overview'])
        ->name('admin.overview');
});

// College Staff
Route::middleware(['auth', 'role:college_staff'])->group(function () {
    Route::get('/college/dashboard', [CollegeDashboardController::class, 'index'])->name('college.dashboard');

    Route::get('/college/maintenance-requests', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'index'])
        ->name('college.maintenance-tickets.index');
    Route::get('/college/maintenance-requests/create', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'create'])
        ->name('college.maintenance-tickets.create');
    Route::post('/college/maintenance-requests', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'store'])
        ->name('college.maintenance-tickets.store');
    Route::get('/college/maintenance-requests/{ticket}', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'show'])
        ->name('college.maintenance-tickets.show');
    // Conversation with the admins about this ticket (the page polls the GET route)
    Route::get('/college/maintenance-requests/{ticket}/messages', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'messages'])
        ->name('college.maintenance-tickets.messages');
    Route::post('/college/maintenance-requests/{ticket}/messages', [\App\Http\Controllers\College\MaintenanceTicketController::class, 'sendMessage'])
        ->middleware('throttle:30,1')
        ->name('college.maintenance-tickets.messages.store');

    // Facilities
    Route::get('/college/facilities', [CollegeFacilityController::class, 'index'])->name('college.facilities.index');
    Route::get('/college/facilities/create', [CollegeFacilityController::class, 'create'])->name('college.facilities.create');
    Route::post('/college/facilities', [CollegeFacilityController::class, 'store'])->name('college.facilities.store');
    Route::get('/college/facilities/{facility}/edit', [CollegeFacilityController::class, 'edit'])->name('college.facilities.edit');
    Route::put('/college/facilities/{facility}', [CollegeFacilityController::class, 'update'])->name('college.facilities.update');
    Route::patch('/college/facilities/{facility}/deactivate', [CollegeFacilityController::class, 'deactivate'])->name('college.facilities.deactivate');

    // Bookings list (now routed through controller)
    Route::get(
        '/college/bookings',
        [\App\Http\Controllers\College\BookingController::class, 'index']
    )->name('college.bookings.index');


    // College booking calendar (read-only)
    Route::get('/college/calendar', [\App\Http\Controllers\College\BookingController::class, 'calendar'])
        ->name('college.calendar');

    // Facilities Utilization Form (College → GSU)
    Route::get('/college/requests/facilities', [\App\Http\Controllers\College\FormController::class, 'createFacilities'])
        ->name('college.requests.facilities.create');
    Route::post('/college/requests/facilities', [\App\Http\Controllers\College\FormController::class, 'storeFacilities'])
        ->name('college.requests.facilities.store');

    // My Facilities Requests (College)
    Route::get('/college/requests', [\App\Http\Controllers\College\FormController::class, 'indexFacilities'])
        ->name('college.requests.index');

    Route::get(
        '/college/requests/facilities/{submission}',
        [\App\Http\Controllers\College\FormController::class, 'showFacilities']
    )->name('college.requests.facilities.show');

    Route::post(
        '/college/requests/facilities/{submission}/cancel',
        [\App\Http\Controllers\College\FormController::class, 'cancelFacilities']
    )->name('college.requests.facilities.cancel');

    // Team: college staff add accounts for their own college
    Route::get('/college/users', [\App\Http\Controllers\College\UserController::class, 'index'])->name('college.users.index');
    Route::get('/college/users/create', [\App\Http\Controllers\College\UserController::class, 'create'])->name('college.users.create');
    Route::post('/college/users', [\App\Http\Controllers\College\UserController::class, 'store'])->name('college.users.store');
    Route::get('/college/users/{user}/edit', [\App\Http\Controllers\College\UserController::class, 'edit'])->name('college.users.edit');
    Route::put('/college/users/{user}', [\App\Http\Controllers\College\UserController::class, 'update'])->name('college.users.update');
    Route::patch('/college/users/{user}/deactivate', [\App\Http\Controllers\College\UserController::class, 'deactivate'])->name('college.users.deactivate');
});

// Org Staff
Route::middleware(['auth', 'role:org_staff'])->group(function () {
    Route::get('/org/dashboard', [OrgDashboardController::class, 'index'])->name('org.dashboard');

    // Bookings
    Route::get('/org/bookings', [\App\Http\Controllers\Org\BookingController::class, 'index'])
        ->name('org.bookings.index');
    Route::get('/org/calendar', [\App\Http\Controllers\Org\BookingController::class, 'calendar'])
        ->name('org.bookings.calendar');

    // Facilities Utilization Form (Org → GSU)
    Route::get('/org/requests/facilities', [OrgFormController::class, 'createFacilities'])
        ->name('org.requests.facilities.create');
    Route::post('/org/requests/facilities', [OrgFormController::class, 'storeFacilities'])
        ->name('org.requests.facilities.store');
    Route::get('/org/requests/facilities/index', [OrgFormController::class, 'indexFacilities'])
        ->name('org.requests.facilities.index');
    Route::get('/org/requests/facilities/{submission}', [OrgFormController::class, 'showFacilities'])
        ->name('org.requests.facilities.show');

    Route::post(
        '/org/requests/facilities/{submission}/cancel',
        [OrgFormController::class, 'cancelFacilities']
    )->name('org.requests.facilities.cancel');

    // Team: org staff add accounts for their own organization
    Route::get('/org/users', [\App\Http\Controllers\Org\UserController::class, 'index'])->name('org.users.index');
    Route::get('/org/users/create', [\App\Http\Controllers\Org\UserController::class, 'create'])->name('org.users.create');
    Route::post('/org/users', [\App\Http\Controllers\Org\UserController::class, 'store'])->name('org.users.store');
    Route::get('/org/users/{user}/edit', [\App\Http\Controllers\Org\UserController::class, 'edit'])->name('org.users.edit');
    Route::put('/org/users/{user}', [\App\Http\Controllers\Org\UserController::class, 'update'])->name('org.users.update');
    Route::patch('/org/users/{user}/deactivate', [\App\Http\Controllers\Org\UserController::class, 'deactivate'])->name('org.users.deactivate');
});

// Guest (External Users): no account needed. The old guest portal is retired; its links go to the public form.
Route::redirect('/guest/{any?}', '/request-facility')->where('any', '.*');