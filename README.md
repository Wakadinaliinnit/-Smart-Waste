# Smart Waste Collection Management System

A web-based system built with HTML, CSS, JavaScript, PHP, and MySQL — based on the
project proposal (OOAD + Agile Scrum methodology).

## Roles
- **Resident** — register, submit collection requests, report full/uncollected bins, view schedules, get notifications
- **Waste Collector** — view assigned tasks/reports, update status
- **Administrator** — manage users, assign requests/reports to collectors, create schedules, monitor system
- **Municipal Officer** — view performance reports and zone statistics (read-only)

## Setup Instructions (XAMPP)

1. Install XAMPP: https://www.apachefriends.org
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the `smart-waste` folder into your XAMPP `htdocs` directory:
   - Windows: `C:\xampp\htdocs\smart-waste`
   - Mac: `/Applications/XAMPP/htdocs/smart-waste`
   - Linux: `/opt/lampp/htdocs/smart-waste`
4. Open `http://localhost/phpmyadmin`, click **Import**, choose `sql/schema.sql`, and run it.
   This creates the `smart_waste_db` database and a default admin account.
5. Visit `http://localhost/smart-waste/` in your browser.

## Default Admin Login
- Email: `admin@smartwaste.com`
- Password: `Admin@123`

**Change this password after first login** — use the admin panel to create a new admin
account, then delete/deactivate the default one, or add a "change password" feature.

## Folder Structure
```
smart-waste/
├── index.php              Landing page
├── login.php / register.php / logout.php
├── includes/
│   ├── db.php              Database connection
│   └── auth.php            Session & role helper functions
├── assets/css/style.css    Shared stylesheet
├── resident/               Resident module
├── collector/              Waste Collector module
├── admin/                  Administrator module
├── officer/                Municipal Officer module
└── sql/schema.sql          Database schema + seed admin user
```

## Creating Test Accounts
- Residents self-register via `register.php`.
- Collectors, Officers, and additional Admins are created by the Admin via
  **Admin → Users → Create New User**.

## Notes
- Passwords are hashed with PHP's `password_hash()` (bcrypt).
- All forms use prepared statements to prevent SQL injection.
- Role-based access control restricts each module's pages to the correct role.
- This is a working baseline covering the Functional Requirements (FR1–FR20) from
  Chapter 4 of the proposal. Extend it with file uploads for report photos,
  email/SMS notifications, and a notification page for the officer/collector if needed.
