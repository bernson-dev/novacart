# NovaCart cron scheduler (first-stage implementation)

PHP 7.3+, MySQL/MariaDB. No changes to the storefront bootstrap.

## Installation

After deploying files, install the idempotent schema:

```sh
php /absolute/path/to/upload/cli.php cron:install
```

Register periodic tasks through `NovaCartScheduler::register($code, $route, $intervalSeconds, $args)` after including `system/library/novacart/scheduler.php`. Routes must start with `cron/`. Only trusted server-side code should register tasks. The installer does not add sample tasks automatically.

Configure a server cron (example: every five minutes):

```sh
*/5 * * * * /usr/bin/php /absolute/path/to/upload/cli.php cron:run
```

View configured jobs:

```sh
php /absolute/path/to/upload/cli.php cron:list
```

HTTP execution is optional. Create a cryptographically random secret in `DIR_STORAGE/novacart_cron.key` with permissions restricted to the PHP account, then call `https://store.example/cron.php?key=SECRET`. Do not expose the secret in logs or screenshots. CLI is recommended.

## Scope / limitations

This first PR contains only the scheduler engine, protected entry points, and schema setup. No queue, heartbeat, administrative UI, or registered maintenance tasks are included. An HTTP secret is not provisioned automatically. A missing cron table causes a visible error: run `cron:install` after deployment. The scheduler uses a global MySQL advisory lock to avoid parallel execution.

For a production release, run integration tests for CLI startup, schema installation, route execution, concurrency, and compatibility with installed extensions.

## Safe integration test (OSPanel / Windows)

Open the OSPanel console in the installed store directory and check out branch `feature/cron-scheduler`. Run:

```bat
php cli.php cron:install
php cli.php cron:test:register
php cli.php cron:list
php cli.php cron:run
php cli.php cron:list
php cli.php cron:run
```

The probe is `test.integration` and its handler is `cron/test`. It returns only a success message; it never modifies catalog data. After the first successful run, the task should have `last_status: success`, a populated `date_last`, and a future `date_next`. The next immediate run should process zero tasks.

For concurrency testing, start two OSPanel terminals and issue `php cli.php cron:run` simultaneously. As the probe completes rapidly, this alone does **not** prove that the locking mechanism works; deterministic concurrency verification requires a barrier or a slow test handler in an isolated test environment.

To remove the test registration after verification, run this query in the TEST database (replace `oc_` with your configured table prefix):

```sql
DELETE FROM oc_novacart_cron WHERE code = 'test.integration';
```
