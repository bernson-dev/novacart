# Jobs monitoring

Monitoring is shown in **System > Maintenance > Task Scheduler**. It uses the existing `tool/cron` access permission.

After deploying this branch run once:

```bat
php cli.php cron:install
```

This creates the new `novacart_jobs_run` table. The existing Windows Task Scheduler job continues to run `php cli.php jobs:run` every minute without changes.

Each run records a start and completion, component statuses/counts, duration, and errors. Only the newest 500 rows are retained. When no invocation has been recorded, the screen reports no history. When the last start is older than five minutes it warns of stale heartbeat.

For testing: run `php cli.php jobs:run`, then refresh the Task Scheduler admin page; ensure the latest row and its duration appear. Stop the Windows task briefly only in a disposable environment if testing the stale warning.

A reported `ok` heartbeat indicates invocation, not that every business operation succeeded. Inspect individual scheduler/queue failures separately.
