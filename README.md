# Medication Routine — TU Thesis (PHP/MySQL)

Prototype for medication reminders with an ESP32 device and a PHP/MySQL backend (XAMPP).
> **Note:** Device-side “Mark as taken” is user-driven; the backend stores taken entries in `medicine_taken` only when recorded via the web UI (or future API).

---

## What it does
- CRUD for medicines/routines (one-time and recurring)
- JSON endpoint for ESP32 to fetch the **next due dose**
- Email notifications around scheduled times (`cron_notify.php`)
- Basic activity/logging in the web app

---

## Quick Start (Windows + XAMPP)

```bash
# In C:\xampp\htdocs
git clone https://github.com/zlatinasx/MEDICINE-ROUTINE-WEBSITE-TU-main.git

Create DB (e.g. medicine_db) → import DB.sql
Copy db.sample.php → db.php and fill DB credentials
Configure SMTP in mail.php (host/port/username/password, From)
Start Apache/MySQL → open
http://localhost/MEDICINE-ROUTINE-WEBSITE-TU-main/

Email Notifications (Scheduled)

cron_notify.php finds upcoming doses and sends emails via PHPMailer.

Windows Task Scheduler example:

Program: C:\xampp\php\php.exe

Arguments: "C:\xampp\htdocs\MEDICINE-ROUTINE-WEBSITE-TU-main\cron_notify.php"

Start in: C:\xampp\htdocs\MEDICINE-ROUTINE-WEBSITE-TU-main

Database (summary)

Key tables defined in DB.sql:

users — accounts

medicine_routines — per-user routines

Indexes support the queries used in get_schedule.php.

Files (flat layout)
index.php
login.php / register.php / profile.php / logout.php
get_schedule.php
cron_notify.php
functions.php
mail.php
DB.sql
db.sample.php   # copy to db.php locally (do NOT commit)

Security

Do not commit secrets (db.php, SMTP passwords). Keep db.php local.

Prototype runs on HTTP. For production use HTTPS/TLS, stronger auth, and rate limiting.

License & Citation

MIT (optional).
“Full code: https://github.com/zlatinasx/MEDICINE-ROUTINE-WEBSITE-TU-main
 — submission tag v1.0.0.”


