# UA-FMS Updates - October 13, 2026

## Summary
Major updates to Guest Portal, Admin Monthly Records, and Guest Booking System.

---

## 1. ✅ Guest Portal Navigation & UI Updates

### Changes Made:
- **Added Notifications System**
  - Notifications button in top-right corner with unread count badge
  - Database-driven notifications using `is_read` boolean column
  - Guests receive notifications when admin approves their facility requests

- **Added Logout Functionality**
  - Logout button in top-right corner of dashboard
  - No sidebar navigation (kept original dashboard layout)

- **Updated Dashboard**
  - Added topbar with Notifications and Logout buttons
  - Kept original "UPCOMING EVENTS" section
  - Maintained 4 Quick Access cards layout
  - Added new "Venue Rates" card (5 total now)

### Files Modified:
- `app/Http/Controllers/Guest/DashboardController.php` - Added unread notifications count
- `resources/views/guest/dashboard.blade.php` - Added topbar and updated Quick Access cards
- `resources/views/layouts/guest.blade.php` - Simple layout without sidebar

---

## 2. ✅ Guest Booking Form - Official UA Format

### Changes Made:
Replaced guest booking form with official **UA Application Form for Facilities Rental (GSU-FM-004)**

**Form Sections:**
- Control Number & Date of Application (auto-generated)
- Client Information (Name, Contact, Address, Organization)
- Facility Selection with Date/Time
- Purpose of rental
- **Facilities/Equipment Table** with quantity tracking:
  - Monobloc Chairs
  - Tables
  - Electric Fans
  - Rostrum
  - Flag & School Color
  - Sound System
  - LED Wall
- Certification statement
- Approval signatories section
- Payment receipt upload (required)
- Supporting documents upload (optional)
- Gate Pass note
- Form reference: GSU-FM-004

**Features:**
- Automatic rental fee calculation based on facility
- Dynamic days and hours calculation
- Professional table layout matching official form
- Form footer with GSU-FM-004 reference

### Files Modified:
- `resources/views/guest/requests/facilities_create.blade.php` - Complete rewrite to match official form

---

## 3. ✅ Venue Rates Page for Guests

### Changes Made:
Created official venue/facilities rental rates page showing GSU pricing information.

**Venues Listed (12 total):**
- PaghiUsA Hall - PhP 15,000.00
- bUsAlian Hall - PhP 10,000.00
- TiripUnAn Hall - PhP 8,000.00
- Balay ni jUAn - PhP 8,000.00
- E-Hub - PhP 5,000.00
- New AVR - PhP 5,000.00
- CEA AVR - PhP 2,000.00
- CCS AVR - PhP 2,000.00
- CBA AVR - PhP 2,000.00
- Class Room/s - PhP 50.00/head in a day
- Open Space - PhP 300.00/day
- Track Oval - PhP 1,000.00/day

**Features:**
- Complete pricing table with capacity and facilities provided
- LED Wall notice (excluded from package)
- Signatories section
- "Book a Facility Now" CTA button
- Added as first Quick Access card on guest dashboard (blue highlight)

### Files Created:
- `resources/views/guest/rates.blade.php` - Venue rates display page

### Files Modified:
- `routes/web.php` - Added guest.rates route
- `resources/views/guest/dashboard.blade.php` - Added Venue Rates quick access card

---

## 4. ✅ Admin Monthly Records System

### Changes Made:
Created comprehensive monthly records page for admin to track facility bookings and generate reports.

**Features:**
1. **Month/Year Filter**
   - Dropdown to select any month/year
   - View button to refresh data

2. **Data Visualizations (Charts)**
   - **Monthly Trend Chart** (Line graph)
     - Shows booking trend over last 12 months
     - Displays total bookings per month
   - **Facility Usage Chart** (Bar graph)
     - Shows bookings per facility for selected month
     - Color-coded bars for each facility

3. **Detailed Records Table**
   - Date Requested
   - Date of Activity
   - Time
   - Purpose
   - Requester
   - Contact Number
   - Venue
   - **Facilities to be Used (7 sub-columns):**
     - Chairs (Monoblock)
     - Tables
     - Fans
     - Rostrum
     - Flags
     - Sound System
     - LED Wall
   - Rental Fee

4. **Summary Section**
   - Usage count for each venue (15 venues tracked)
   - Shows how many times each facility was booked
   - Zero values displayed in gray

5. **Total Rental Fees**
   - Calculates total revenue from all bookings
   - Displayed at bottom of table

6. **Print Functionality**
   - Print button to generate printable report
   - Print-friendly styling

### Files Created:
- `app/Http/Controllers/Admin/MonthlyRecordsController.php` - Controller handling data queries and calculations
- `resources/views/admin/monthly-records/index.blade.php` - Monthly records view with charts and table

### Files Modified:
- `routes/web.php` - Added admin.monthly-records.index route and controller import
- `resources/views/layouts/admin.blade.php` - Added "Monthly records" link under Overview section

**Libraries Used:**
- Chart.js 4.4.0 (for line and bar charts)

---

## 5. ✅ Login Page Styling Updates

### Changes Made:
- Styled "Back to home" button with white background and black text
- Added proper border and shadow for better visibility
- Consistent hover states

### Files Modified:
- `resources/views/auth/login.blade.php` - Updated CSS for back button

---

## Admin Access

**Login Credentials:**
- Email: `adminA@example.com`
- Password: `password`

**Admin Menu Navigation:**
- Overview → Dashboard
- Overview → **Monthly records** (NEW)
- Facilities → All facilities
- Facilities → Add facility
- Requests → Approve Requests
- Requests → Repair & maintenance
- Requests → Reservations
- Requests → Create Direct Reservation
- Requests → Reservation calendar

---

## Testing Checklist

### Guest Portal
- [x] Guest can view dashboard with Notifications and Logout buttons
- [x] Guest receives notifications when admin approves requests
- [x] Guest can access Venue Rates page from Quick Access
- [x] Guest booking form displays all official UA form fields
- [x] Guest booking form calculates rental fees automatically
- [x] Guest can upload payment receipt (required)
- [x] Guest can upload supporting documents (optional)

### Admin Monthly Records
- [x] Admin can access Monthly records from sidebar
- [x] Admin can filter by month/year
- [x] Charts display correctly (line and bar)
- [x] Table shows all booking details with equipment columns
- [x] Venue usage summary displays correctly
- [x] Total rental fees calculated correctly
- [x] Print functionality works

### General
- [x] All routes working correctly
- [x] No database errors
- [x] Responsive design working
- [x] Browser caching cleared for updates to appear

---

## Files Changed Summary

### New Files (6):
1. `app/Http/Controllers/Admin/MonthlyRecordsController.php`
2. `resources/views/admin/monthly-records/index.blade.php`
3. `resources/views/guest/rates.blade.php`
4. `CHANGELOG_2026-10-13.md` (this file)

### Modified Files (8):
1. `app/Http/Controllers/Guest/DashboardController.php`
2. `resources/views/auth/login.blade.php`
3. `resources/views/guest/dashboard.blade.php`
4. `resources/views/guest/requests/facilities_create.blade.php`
5. `resources/views/layouts/admin.blade.php`
6. `resources/views/layouts/guest.blade.php`
7. `routes/web.php`
8. `app/Http/Controllers/AuthController.php`

---

## Next Steps

1. ✅ Clear browser cache (Ctrl + Shift + R)
2. ✅ Test all guest portal features
3. ✅ Test admin monthly records with charts
4. ✅ Verify data accuracy in reports
5. ✅ Test print functionality
6. **Push changes to GitHub** ← You are here
7. Deploy to production server (if applicable)

---

## Notes

- All changes maintain backward compatibility
- No database migrations required (reusing existing tables)
- Guest portal kept original dashboard layout per user request
- Monthly records uses Chart.js for visualizations
- Official UA form template (GSU-FM-004) implemented exactly as specified
- Venue rates match official GSU pricing document
