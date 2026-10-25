<?php
/**
 * Regression tests for DST-sensitive NovaCart Cron schedules.
 * Run: php tests/cron_dst_regression.php
 */
require_once dirname(__DIR__) . '/upload/system/library/novacart/cron_expression.php';

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
