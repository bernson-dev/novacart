# NovaCart Scheduler, Queue and Jobs Runner

PHP 7.3+, MySQL/MariaDB. Task handlers run in the catalog context.

## Initial setup

After installing or upgrading the store, run from its installed root directory:

```sh
php cli.php cron:install
```

This installs or updates Scheduler, Queue, and monitoring tables. It also generates a cryptographically random HTTP Cron secret in `DIR_STORAGE/novacart_cron.key` if one does not exist. Existing nonempty keys are preserved. The storage directory must be writable and must not be publicly accessible.

## Running jobs

Recommended unified runner (runs due Scheduler tasks and pending Queue jobs, and records a monitoring heartbeat):

```sh
php cli.php jobs:run
```

For OS scheduled launches, use `php cli.php jobs:run --source=scheduled`. Schedule the command in your operating-system scheduler (Windows Task Scheduler or system cron). The administration page shows the command and execution history.

Individual commands:

```sh
php cli.php cron:run
php cli.php cron:list
php cli.php queue:run
php cli.php queue:stats
```

## Administration

Open **System > Maintenance > Task Scheduler** (requires `tool/cron` access permission; changes require `modify` permission).

- Add and edit tasks with a `cron/...` catalog-controller route, an interval or five-field Cron expression, and JSON arguments.
- Cron expressions have a five-run preview and follow the configured store timezone, including DST transitions.
- Choose a missed-run policy: `run_once` (default) or `skip` (ignore runs more than 120 seconds overdue).
- Review Scheduler/Queue status, run history, results, and monitoring heartbeat.
- HTTP Web Cron key management is displayed to administrators with modify permission even before the key exists. Creating or rotating the secret refreshes the generated HTTP command. Rotating an existing key invalidates previously copied HTTP URLs.

## Optional HTTP entry point

Use HTTPS and the generated command from the administration page to call `cron.php?key=SECRET`. The endpoint validates the key before executing jobs. Do not expose the secret in screenshots, URLs shared with third parties, logs, or documentation. The CLI runner is preferred because URLs containing secrets may be logged by web servers.

## Implementation and tests

- Scheduler uses a MySQL advisory lock to serialize due task processing.
- Queue uses atomic claims, retries with backoff, and recovery of abandoned jobs. Side-effecting handlers must be idempotent.
- `php tests/cron_dst_regression.php` checks DST-sensitive Cron schedules (from the source repository).
- Run CLI/HTTP integration tests on a disposable installation before deployment; adding new or external-effecting handlers requires application-specific testing.
