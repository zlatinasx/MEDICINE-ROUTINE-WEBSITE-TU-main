@echo off
cd /d "C:\xampp\htdocs\MEDICINE-ROUTINE-WEBSITE-TU-main"
"C:\xampp\php\php.exe" -f cron_notify.php >> cron.log 2>&1