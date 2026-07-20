# Implementation Plan — System Administration Enhancements

This plan outlines the implementation details for enhancing the Admin section with:
1. **Master Lookup Dictionaries Manager** (CRUD UI to manage Companies, Professions, Academies, Categories).
2. **Audit Trail Explorer** (Search and filter controls in System Settings).
3. **One-Click Database Backup & Restore** (Self-contained, native PHP database backup utility).
4. **Registration Moderation Queue Alert** (Dashboard notification alert for pending trainee/instructor registrations).
5. **Certificate Template Customizer Check** (Verify support for signature, logo, and background template file uploads).
6. **Course Capacity & Intake Alerts** (Progress indicators and warning flags for enrollment levels).
7. **Evaluation Rating Summaries** (Top metrics bar in Course Evaluations page).
8. **System Maintenance Mode Switch** (Settings toggle to restrict public traffic during updates).

---

## User Review Required

> [!WARNING]
> - The **Database Restore** operation resets active system data to the uploaded backup file. This operation should only be performed by authorized administrators.
> - Enabling **Maintenance Mode** will prevent all non-admin users (including Instructors and Trainees) from accessing the portal, displaying a maintenance page instead.

---

## Proposed Changes

### 1. Dashboard & Moderation Queue

#### [MODIFY] [DashboardController.php](file:///c:/xampp/htdocs/Itop_management_system/app/Controllers/DashboardController.php)
- Fetch the count of users with `status = 'pending'`.
- Pass `pendingUsersCount` variable to `View::render('dashboard/admin')`.

#### [MODIFY] [admin.php (Dashboard View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/dashboard/admin.php)
- Add a banner alert at the top of the dashboard showing the pending user count with a button linking directly to the Account Moderation page (`admin-users`).

---

### 2. Database Backup & Restore

#### [MODIFY] [index.php](file:///c:/xampp/htdocs/Itop_management_system/index.php)
- Register GET route `'admin-backup-database' => [AdminController::class, 'backupDatabase']`.
- Register POST route `'admin-restore-database' => [AdminController::class, 'restoreDatabase']`.

#### [MODIFY] [AdminController.php](file:///c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php)
- **`backupDatabase()`**: Retrieve all tables in the active schema, generate standard SQL schema drops and inserts, and return it directly as a `.sql` attachment download.
- **`restoreDatabase()`**: Receive a `.sql` file upload, disable foreign keys temporarily, execute the SQL queries, log the event to activity logs, and redirect with success messages.

#### [MODIFY] [system-settings.php (View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/admin/system-settings.php)
- Implement a dedicated **Database Backups Card** offering:
  - **"Backup Database"** button (downloads SQL file).
  - **"Restore Database"** file drop selection form with browser confirmations.

---

### 3. Audit Trail Search Explorer

#### [MODIFY] [AdminController.php (systemSettings method)](file:///c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php#L867)
- Add filter checks based on `$_GET['search']`. Query using wildcard matches against `u.name`, `al.action`, and `al.details`.

#### [MODIFY] [system-settings.php (View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/admin/system-settings.php)
- Add a Search Form above the System Activity Logs table allowing real-time searches.

---

### 4. Course Capacity & Intake Alerts

#### [MODIFY] [courses.php (View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/admin/courses.php)
- Render enrollment ratios alongside course listings (e.g. *15 / 20 Enrolled*).
- Display a small progress bar showing occupancy percentage, coloring it orange/red when occupancy is $\ge 90\%$.

---

### 5. Evaluation Rating Summaries

#### [MODIFY] [AdminController.php (evaluations method)](file:///c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php#L431)
- Fetch overall average course rating and overall average instructor rating from the database, passing these to `admin/evaluations`.

#### [MODIFY] [evaluations.php (View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/admin/evaluations.php)
- Add a metric strip at the top of the evaluations logs displaying aggregate stars and count averages.

---

### 6. System Maintenance Mode Switch

#### [MODIFY] [index.php](file:///c:/xampp/htdocs/Itop_management_system/index.php)
- Map POST route `'admin-save-system-settings' => [AdminController::class, 'saveSystemSettings']`.

#### [MODIFY] [AdminController.php](file:///c:/xampp/htdocs/Itop_management_system/app/Controllers/AdminController.php)
- Add **`saveSystemSettings()`** action to save `maintenance_mode` toggle state inside the `website_settings` table.
- Retrieve the current setting value in `systemSettings()` to pass it to the view.

#### [MODIFY] [system-settings.php (View)](file:///c:/xampp/htdocs/Itop_management_system/app/Views/admin/system-settings.php)
- Include a **Maintenance Mode Toggle Switch** in the System Settings card.

#### [MODIFY] [bootstrap.php](file:///c:/xampp/htdocs/Itop_management_system/app/bootstrap.php)
- Add check for `maintenance_mode = '1'`. If enabled, check if the current user has `role = 'admin'`. If not, display a clean maintenance splash template and exit execution.

---

## Verification Plan

### Manual Verification
1. **Moderation Queue Alert**: Check that creating a new pending user displays the notification banner on the administrator's dashboard.
2. **Database Backup**: Trigger a database backup from the System Settings panel and verify that a `.sql` file downloads.
3. **Database Restore**: Perform database restore using the generated backup and confirm records load.
4. **Audit Logs Search**: Search the system activities logs using key terms (e.g. "manual", "saved") and verify only matching rows appear.
5. **Course Capacity**: Verify that courses with high enrollments display orange/red capacity alerts.
6. **Ratings Summary**: Open Evaluation Reports page and verify that overall average metrics load correctly.
7. **Maintenance Mode**: Turn on Maintenance Mode, log out or visit from a trainee profile, and check that a styled "Under Maintenance" page loads. Check that logging back in as admin bypasses it.
