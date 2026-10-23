# Automatic NovaCart Jobs Runner

The existing periodic Scheduler and Queue remain independent. `jobs:run` invokes both in sequence from one CLI process; each retains its own locking and error handling.

## Setup

Once per installation:

```bat
php cli.php cron:install
```

Manual smoke test:

```bat
php cli.php jobs:run
```

Expected on an empty installation: `scheduler.processed=0` and `queue.processed=0`.

## Windows (OSPanel)

Use Windows Task Scheduler (Create Task). Choose a dedicated user account with access to the store. Set a recurring trigger (e.g. every 1 minute), Action **Start a program**:

- Program: absolute path to the *same PHP CLI executable used in OSPanel* (find with `where php` in OSPanel terminal)
- Arguments: `"C:\OSPanel\domains\novacart20260911.loc\cli.php" jobs:run`
- Start in: `C:\OSPanel\domains\novacart20260911.loc`

Use *Do not start a new instance* if the task is still running, and configure error handling and a dedicated execution account. Scheduling at the OS level is required; no in-process infinite loop or web-request cron fallback is introduced.

## Linux / hosting

Use a scheduled task with absolute paths (update them for the host):

```cron
* * * * * /usr/bin/php /absolute/path/to/upload/cli.php jobs:run
```

Do not register both this unified task and separate `cron:run` / `queue:run` tasks unless intentional: they could duplicate work attempts and contend for locks.

## Notes

Scheduler and Queue code are unchanged. Long tasks delay the second executor because the unified command is sequential. For high volume, configure the two existing CLI commands as separate OS tasks instead. Avoid running as a privileged OS user.
