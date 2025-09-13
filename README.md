# Medication Routine — TU Thesis (PHP/MySQL)

Prototype for medication reminders with an ESP32 device and a PHP/MySQL backend (XAMPP).  
> **Note:** Device-side “Mark as taken” is user-driven. The backend stores taken entries in **`medicine_taken`** only when recorded via the web UI (or a future API).

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
cd MEDICINE-ROUTINE-WEBSITE-TU-main
composer install

Create DB (e.g. medicine_db) → import DB.sql.
Copy db.sample.php → db.php and fill DB credentials.
Configure SMTP in mail.php (host/port/username/password, From).
Start Apache/MySQL and open
http://localhost/MEDICINE-ROUTINE-WEBSITE-TU-main/

**Endpoint (ESP32)**
GET /get_schedule.php?user_id=3&date=YYYY-MM-DD
Example response:
{
  "id": 12,
  "medicine_name": "Levothyroxin 50",
  "scheduled_date": "2025-09-10 08:00:00"
}

One-time routines use scheduled_date (DATETIME).
Recurring routines use scheduled_time (TIME) within start_date..end_date.
Results exclude doses already present in medicine_taken for the same day/time.


**Email Notifications**
cron_notify.php finds upcoming doses and sends emails via PHPMailer.

**Windows Task Scheduler**
Program:   C:\xampp\php\php.exe
Arguments: "C:\xampp\htdocs\MEDICINE-ROUTINE-WEBSITE-TU-main\cron_notify.php"
Start in:  C:\xampp\htdocs\MEDICINE-ROUTINE-WEBSITE-TU-main

**Database (summary)**
Key tables defined in DB.sql:
users — accounts
medicine_routines — per-user routines
mode ENUM('one','recurring')
scheduled_date (one-time) or scheduled_time + start_date..end_date (recurring)
notified TINYINT(1) (helper flag)
medicine_taken — confirmed intakes: routine_id, taken_date, taken_at
activity_log — app events (login, medicine_add, email_sent, …)
Indexes support the queries used in get_schedule.php.

**Files (flat layout)**
index.php
login.php / register.php / profile.php / logout.php
get_schedule.php
cron_notify.php
functions.php
mail.php
DB.sql
db.sample.php    # copy to db.php locally (do NOT commit)
composer.json / composer.lock

**ESP32 firmware (note)**
The ESP32 firmware is not included in this public repository. It fetches the
daily schedule via GET /get_schedule.php and implements a 3-phase reminder
(green LED → buzzer → red LED). Full source is available on request / in thesis appendix.

**Security**
Do not commit secrets (db.php, SMTP passwords). Keep db.php local.
Prototype runs on HTTP. For production use HTTPS/TLS, stronger auth, and rate limiting.
License & Citation

MIT (optional).
“Full code: https://github.com/zlatinasx/MEDICINE-ROUTINE-WEBSITE-TU-main
 — submission tag v1.0.0.”




