# NovaCart SEO: installation and upgrade

## Existing stores

1. Back up database, files and configuration. Test on a copy before production.
2. Deploy the updated upload files without overwriting the existing config.php files.
3. Apply the following idempotent CREATE TABLE IF NOT EXISTS migrations if the tables are missing. Replace the oc_ prefix with the configured DB_PREFIX:
   - docs/migrations/seo-url-history.sql
   - docs/migrations/seo-redirect.sql
   - docs/migrations/seo-not-found.sql
4. Refresh OCMOD modifications and clear relevant application and theme caches.
5. Ensure the administrator group has access and modify permissions for design/seo_url and, as applicable, extension/feed/google_sitemap and tool/cron.
6. From the installed shop root run:
   
   `php cli.php cron:install`
   
   Then run `php cli.php cron:list`. Verify seo.404.cleanup and seo.sitemap.cleanup (daily). Existing schedules and nonempty HTTP cron keys are preserved. The OS scheduler must still be configured to run jobs:run.
7. Verify representative public SEO URLs, one language alternate, one manual redirect, and the configured XML sitemap. Check error logs.

Fresh installations already include the three SEO tables in install/opencart.sql. Cron setup is still required.

## SEO features

- History records replaced aliases for the same entity, store and language. Active aliases take priority.
- Manual redirects support 301, 302 and 410 for unresolved SEO paths. Paths are local, including the store subdirectory and language prefix if applicable. Query strings are not rule identifiers. Chains are rejected rather than automatically rewritten.
- The 404 journal aggregates paths by store and language; default retention is 90 days. Query strings, IP addresses and cookies are not recorded, but sensitive information embedded in a URL path can still appear.
- Missing aliases and SEO diagnostics support preview and selective fixes. Diagnostics apply to the current page (up to 50 records). Metadata descriptions are shared across stores.
- Sitemap PRO generates partitioned XML with language alternates and optional file caching. Enable it in the Google Sitemap extension. Clear sitemap cache after bulk catalog changes if an immediate update is needed.
- CSV import accepts UTF-8 with optional BOM, comma or semicolon delimiter, up to 1000 rows and 2 MiB per file. Preview expires after 15 minutes. Import is insert-only; existing rules are not overwritten. Export includes all rules; split large exports before reimporting.
- Noindex is store-specific and applies to all languages of a selected entity. It does not disable the page and does not inherit to child categories. All built-in XML sitemaps respect the exclusions. Remove the rule to restore indexing eligibility. Do not block crawling of these URLs in robots.txt. Third-party full-page caches and feeds require separate compatibility checks.

## Verification and limits

CI covers SEO history, redirects, the 404 journal, CSV, diagnostics, noindex and Sitemap, including MariaDB fixtures. This is not a substitute for production-scale load testing. CSV export reads database rows in batches but the response is buffered in memory. Keep a full database backup for restoration rather than relying solely on CSV.
