# NovaCart Queue (phase 2b)

The queue is separate from the periodic Scheduler. It supports atomic claims, retry/backoff and stale worker recovery. It does not register tasks by itself.

## CLI

After updating this branch, execute in the store's root:

```bat
php cli.php cron:install
php cli.php queue:stats
php cli.php queue:run
```

To enqueue trusted application jobs, load `system/library/novacart/queue.php` and call `(new NovaCartQueue($registry))->enqueue('code', 'cron/handler', array('id'=>123))`. Handlers must be trusted routes, and business-level idempotency is mandatory for operations with external side effects.

**This is a draft implementation awaiting concurrency and failure/recovery integration tests. Do not use it for production mail or payment processing yet.**

Queue status counts: pending, processing, done, failed. Queue execution is intentionally not connected to `cron:run` yet; configure `queue:run` separately after validation.
