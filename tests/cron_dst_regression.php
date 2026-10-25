<?php
/**
 * Regression tests for DST-sensitive NovaCart Cron schedules.
 * Run: php tests/cron_dst_regression.php
 */
$root = dirname(__DIR__);
$candidates = array(
    $root . '/system/library/novacart/cron_expression.php',        // Installed storefront
    $root . '/upload/system/library/novacart/cron_expression.php' // Source repository
);
$library = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $library = $candidate;
        break;
    }
}
if ($library === null) {
    fwrite(STDERR, "Cron library not found in system/ or upload/system/. Check that the PR branch files are installed.\\n");
    exit(2);
}
require_once $library;

function expectNext($expression, $instantUtc, $expected) {
    $instant = new DateTimeImmutable($instantUtc, new DateTimeZone('UTC'));
    $actual = NovaCartCronExpression::nextInTimezone($expression, $instant, 'Europe/Kyiv', 1);
    if ($actual !== array($expected)) {
        fwrite(STDERR, "FAIL " . $expression . " after " . $instantUtc .
            ": expected " . $expected . ", got " . implode(',', $actual) . "\n");
        exit(1);
    }
}

expectNext('30 3 * * *', '2026-03-29 00:30:00', '2026-03-30 03:30:00');
expectNext('30 3 * * *', '2026-10-25 00:00:00', '2026-10-25 03:30:00');
expectNext('30 3 * * *', '2026-10-25 00:40:00', '2026-10-26 03:30:00');
expectNext('0 9 * * *', '2026-06-01 05:00:00', '2026-06-01 09:00:00');
echo "Cron DST regression: OK\n";
