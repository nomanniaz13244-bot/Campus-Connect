# Campus Connect — University Community Platform

Internship Project | Zynvex Solutions | Web Development Internship
Intern: Noman Niaz (ZYNVEX-CERT-0915)

## About
Campus Connect is an all-in-one university community platform bringing together a
student marketplace, academic notes sharing, lost & found, clubs & societies,
events/internships discovery, and a complaints portal — all behind role-based
authentication (Student / Club Admin / Admin).

## Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP (procedural, mysqli)
- **Database:** MySQL
- **Tools:** GitHub, XAMPP / phpMyAdmin (local dev), Hostinger (deployment)

## Current Progress — All Modules Complete ✅

**Module 1 — Foundation**
- [x] Database schema (ERD) for all 7 modules — see `database/schema.sql`
- [x] Project folder structure
- [x] Role-based authentication (register/login/logout) — Student & Club Admin self-register,
      Admin is seeded directly in the database
- [x] Base UI layout — shared navbar, dashboard shell, sidebar, shared CSS design system
- [x] Role-specific dashboards (student / club_admin / admin) with route guarding and live stats

**Module 2 — Marketplace & Notes**
- [x] Buy & Sell: post/browse/search listings, item detail, mark sold, delete (owner-only)
- [x] Notes Sharing: upload, browse/search by subject & semester, download with counter
- [x] Image & file upload handling with type/size validation (`includes/upload_helper.php`)
- [x] Full PHP-MySQL CRUD with prepared statements

**Module 3 — Lost & Found, Clubs, Events**
- [x] Lost & Found: report lost/found items, browse/filter, mark resolved (reporter-only)
- [x] Clubs & Societies: directory, club detail page, join requests, club-admin-managed
      club creation, member approval/rejection, club update posts
- [x] Events & Internships: discovery board with category filtering, posting (club_admin/admin)
- [x] Role-based access control enforced on every module via `includes/auth_check.php`

**Module 4 — Complaints, Integration & Deployment prep**
- [x] Complaints Portal: student submit/track, admin status update + response panel
- [x] End-to-end route guarding and PHP syntax validated across all files (`php -l`)
- [x] README/documentation complete, ready for local testing and deployment

## Known limitations / suggested next steps before production
- Default admin password is a placeholder hash — must be set manually (see setup step 5).
- No CSRF tokens on forms yet — recommended before public deployment.
- No pagination on listing pages — fine for a class project, consider adding for large datasets.
- No email notifications (e.g. on complaint status change) — could be a nice extension.

## Local Setup (XAMPP)
1. Copy the `campus-connect` folder into `htdocs/` (e.g. `C:\xampp\htdocs\campus-connect`).
2. Start Apache and MySQL from the XAMPP control panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) and import `database/schema.sql`.
4. Update DB credentials in `config/db.php` if needed (defaults: user `root`, no password).
5. **Set the admin password:** the seeded admin row has a placeholder hash. Run this once
   in a PHP file or phpMyAdmin console to set a real password:
   ```php
   UPDATE users SET password_hash = '<paste output of password_hash("YourPassword", PASSWORD_BCRYPT)>'
   WHERE email = 'admin@campusconnect.edu';
   ```
6. Visit `http://localhost/campus-connect/`.

## Folder Structure
```
campus-connect/
├── assets/
│   ├── css/style.css
│   ├── js/
│   └── uploads/{marketplace,notes,lostfound,clubs,events}/
├── auth/               # register.php, login.php, logout.php
├── config/             # db.php (database connection)
├── dashboard/          # student.php, club_admin.php, admin.php
├── database/           # schema.sql (full ERD, all 7 tables)
├── includes/           # header.php, footer.php, auth_check.php, upload_helper.php
├── modules/
│   ├── marketplace/    # index, create, view, update_status, delete
│   ├── notes/          # index, upload, download
│   ├── lostfound/       # index, report, resolve
│   ├── clubs/            # index, view, my_club (club_admin), manage (admin)
│   ├── events/            # index, create
│   └── complaints/         # index (student), manage (admin)
└── index.php               # landing page
```

## Roles
| Role       | Access                                                    |
|------------|-------------------------------------------------------------|
| student    | Marketplace, Notes, Lost & Found, join Clubs, Events, Complaints |
| club_admin | Manage own club, members, post club events                 |
| admin      | Full system oversight, manage complaints & clubs           |
