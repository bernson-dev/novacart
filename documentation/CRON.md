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

## Validation

The scheduler was functionally verified with OSPanel on Windows: schema install, task registration/execution, success/failure reporting, scheduling intervals and MySQL advisory lock contention. Temporary test routes and CLI commands were removed from the release candidate. Run `cron:install` before `cron:run` on each new installation.
