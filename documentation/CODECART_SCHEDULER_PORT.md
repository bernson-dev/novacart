# CodeCart PRO 3.0.6.0 → NovaCart: scheduler port audit

Source: `CodeCartPro/CodeCartPro-3.0.6.0` at `main`; destination NovaCart existing scheduler/queue in `main`.

## Direct-copy verdict

| Original component | Verdict | Reason |
| --- | --- | --- |
| `admin/view/template/tool/scheduler.twig` | Adapt layout, not replace | Uses `scheduler_id`, `tool/scheduler`, and CodeCart queue fields; NovaCart has working monitor that must remain |
| `admin/controller/tool/scheduler.php` | Adapt actions, not direct-copy | Instantiates `CodeCart\Core\Scheduler`, uses `codecart_scheduler`, `codecart_queue` and `codecart_scheduler_key`; invokes CodeCart HTTP modes |
| `system/library/codecart/src/Scheduler.php` | Do not directly replace | Different schema, method signatures, namespace, built-in notifications and lock implementation; NovaCart data would be orphaned |
| `system/library/codecart/src/Queue.php` | Do not directly replace | Different queue table, claim ownership, method signatures, retry semantics |
| `catalog/controller/cron/codecart.php` | Adapt protected modes | Uses CodeCart bootstrap, heartbeat, key and services |
| `catalog/controller/cron/cart.php` | Portable with functional review | Uses core cart table; deletes carts, so require retention sign-off and testing |
| `catalog/controller/cron/queue_worker.php` | Portable with namespace/route adaptation | Wraps CodeCart Queue class |
| `catalog/controller/cron/health.php` | Not standalone | Requires CodeCart Preflight and SystemNotification |
| `catalog/controller/cron/security.php` | Not standalone | Requires CodeCart LoginProtection, SecurityAudit, GdprManager and extra tables |
| `catalog/controller/cron/currency.php`, `mail_campaign.php`, `stock_notify.php` | Requires separate dependency review | These have application-specific side effects and must not be enabled before validation |

## What this branch changes

- Provides CodeCart-style task CRUD/actions on the existing NovaCart scheduler, keeping `novacart_cron` records and `jobs:run` intact.
- Adds protected HTTP execution of a specific scheduler job and queue worker using the **existing** `DIR_STORAGE/novacart_cron.key`.
- Preserves NovaCart's running heartbeat/history UI.
- Does **not** auto-register potentially destructive CodeCart tasks.
- No DB schema migration or parallel scheduler is introduced.

## Functional coverage of CodeCart administration

- Scheduler: list, create, edit, enable/disable, delete, run one, interval, route and JSON args.
- Queue: combined panel, status totals, search, status filter, sort, page size and pagination; protected run-all, run-one/retry and safe deletion.
- Copyable CLI and HTTPS commands; the HTTP secret is provisioned at explicit `cron:install` and should never be shared.
- NovaCart's existing 30-second auto-refresh monitor remains intact.
- Existing Queue admin at `tool/queue` is retained as an alternate interface.

## Important differences and limits

- Core CodeCart tasks requiring `CodeCart\\Core` dependencies are **not** registered automatically: health, security, currency, mail, GDPR and stock notifications require separate compatibility/side-effect review.
- Full HTML/CSS parity to CodeCart is secondary to retaining NovaCart's monitor and actual data.
- Queue execution is protected with the existing storage secret; Windows Task Scheduler continues using `php cli.php jobs:run` unchanged.
- There is no data migration to `codecart_scheduler` or `codecart_queue`; existing NovaCart records stay in place.

## Acceptance tests

1. PHP lint and CI on every changed file, PHP 7.3+ compatibility.
2. `jobs:run` and `queue:stats` unchanged; existing tasks and heartbeat intact.
3. Add/edit/disable/delete a disposable `cron/*` task and verify actual `novacart_cron` DB state.
4. Verify protected single-task run with storage key configured; invalid key must return 403.
5. Check redirects and forms on HTTPS canonical admin URL; test RU/UK/EN.
6. Do not merge until tested in OSPanel; do not enable copied cleanup/security handlers until dependencies and side effects are reviewed.


## Jobs Monitor source attribution (PR #125)

After deploying this version, run `php cli.php cron:install` **once** to add
`source` to an existing `novacart_jobs_run` table. Historical rows retain
`unknown`; no retrospective attribution is attempted.

* `php cli.php jobs:run` records `cli` (manual or unmarked CLI).
* `php cli.php jobs:run --source=scheduled` records `scheduled`. Set this
  argument only for the recurring Windows Task Scheduler / server cron entry.
* Authenticated `cron.php?key=...` records `http` and runs both Scheduler and Queue.

The admin HTTP command is formatted for Windows PowerShell as
`curl.exe -fsS "<full URL>"`; copy the **entire** command, including the URL.
Do not publish the URL because it includes the secret key. Do not configure
both HTTP and CLI background schedules at once.
