# Oxygen Scheduler Guide

The OxygenFramework Scheduler allows you to fluently define command schedules within your application itself. When using the scheduler, only a single Cron entry is needed on your server.

## 1. How it Works (The "A to Z")

The system works in three steps:
1.  **The Trigger**: A single Cron job on your server runs `php oxygen schedule:run` every minute.
2.  **The Manager**: The `ScheduleRunCommand` checks all defined tasks to see if they are "due" (e.g., is it 2:00 AM?).
3.  **The Execution**: If a task is due, it is executed immediately.

## 2. Setting Up the Trigger

### On Linux / Mac (Crontab)
Open your crontab:
```bash
crontab -e
```
Add this line (replace `/path/to/project` with your actual path):
```bash
* * * * * php /path/to/project/oxygen schedule:run >> /dev/null 2>&1
```

### On Windows (Task Scheduler)
1.  Open **Task Scheduler**.
2.  Create a **Basic Task**.
3.  Trigger: **Daily**, repeat every **1 minute** (Windows is tricky with minutes, you might need "Indefinitely" in advanced settings).
4.  Action: **Start a Program**.
    - Program: `php.exe`
    - Arguments: `c:\path\to\project\oxygen schedule:run`

## 3. Defining Schedules

Open `app/Console/Commands/ScheduleRunCommand.php` and add your tasks in the `defineTasks` method.

### Example 1: Running a Command
```php
// Run the log cleanup command every day at midnight
$schedule->command('logs:cleanup')->daily();
```

### Example 2: Running a Closure (Code)
```php
// Run arbitrary PHP code every hour
$schedule->call(function() {
    // Do something...
    file_put_contents('storage/logs/hourly.txt', 'Ran at ' . date('H:i'));
})->hourly();
```

### Example 3: Running a Shell Command
```php
// Ping a server every 5 minutes
$schedule->exec('ping -c 3 google.com')->cron('*/5 * * * *');
```

## 4. Available Frequencies

| Method | Description |
| :--- | :--- |
| `->everyMinute()` | Run every minute |
| `->hourly()` | Run every hour at minute 0 |
| `->daily()` | Run every day at 00:00 |
| `->dailyAt('13:00')` | Run every day at 13:00 |
| `->weekly()` | Run every Sunday at 00:00 |
| `->monthly()` | Run on the first day of every month at 00:00 |
| `->cron('* * * * *')` | Custom Cron expression |

## 5. Creating Your Own Scheduled Commands

1.  Create a command: `php oxygen command:create MyTask` (or manually create the file).
2.  Register it in `app/Console/OxygenKernel.php`.
3.  Add it to `ScheduleRunCommand.php`.
