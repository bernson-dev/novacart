<?php
/**
 * Five-field Cron expressions in database-local wall time.
 * Standard DOM/DOW OR semantics when both fields are restricted.
 * PHP 7.3 compatible; no executable commands or external dependencies.
 */
class NovaCartCronExpression {
    private static function field($source, $low, $high, $weekday = false) {
        $source = trim($source);
        if ($source === '') { throw new InvalidArgumentException('Empty Cron field'); }
        $allowed = array();
        foreach (explode(',', $source) as $part) {
            if (!preg_match('/^(\*|\d+(?:-\d+)?)(?:\/(\d+))?$/', $part, $m)) {
                throw new InvalidArgumentException('Invalid Cron field: ' . $part);
            }
            $step = isset($m[2]) ? (int)$m[2] : 1;
            if ($step < 1 || $step > 10000) { throw new InvalidArgumentException('Invalid Cron step'); }
            if ($m[1] === '*') {
                $from = $low; $to = $high;
            } elseif (strpos($m[1], '-') !== false) {
                $bounds = explode('-', $m[1]);
                $from = (int)$bounds[0]; $to = (int)$bounds[1];
            } else {
                $from = (int)$m[1];
                $to = isset($m[2]) ? $high : $from;
            }
            if ($from < $low || $to > $high || $from > $to) {
                throw new InvalidArgumentException('Cron value out of range: ' . $part);
            }
            for ($n = $from; $n <= $to; $n += $step) {
                $allowed[$weekday && $n === 7 ? 0 : $n] = true;
            }
        }
        ksort($allowed);
        return $allowed;
    }

    public static function parse($expression) {
        $parts = preg_split('/\s+/', trim((string)$expression));
        if (count($parts) !== 5) {
            throw new InvalidArgumentException('Cron needs five fields: minute hour day month weekday');
        }
        return array(
            'minute' => self::field($parts[0], 0, 59),
            'hour' => self::field($parts[1], 0, 23),
            'day' => self::field($parts[2], 1, 31),
            'month' => self::field($parts[3], 1, 12),
            'weekday' => self::field($parts[4], 0, 7, true),
            'day_any' => $parts[2] === '*',
            'weekday_any' => $parts[4] === '*'
        );
    }

    public static function next($expression, $after, $count = 1) {
        $fields = self::parse($expression);
        $count = max(1, min(5, (int)$count));
        // Represent DB local datetime as a naive wall clock, avoiding PHP/DB timezone drift.
        $cursor = new DateTimeImmutable((string)$after, new DateTimeZone('UTC'));
        $cursor = $cursor->setTime((int)$cursor->format('H'), (int)$cursor->format('i'), 0)->modify('+1 minute');
        $result = array();
        // Leap-day expressions can require four years; bound searches at five years.
        $end = $cursor->modify('+5 years');
        while ($cursor < $end && count($result) < $count) {
            $month = (int)$cursor->format('n');
            $day = (int)$cursor->format('j');
            $weekday = (int)$cursor->format('w');
            $dom = isset($fields['day'][$day]);
            $dow = isset($fields['weekday'][$weekday]);
            if ($fields['day_any']) { $dayMatches = $dow; }
            elseif ($fields['weekday_any']) { $dayMatches = $dom; }
            else { $dayMatches = $dom || $dow; }
            if (!isset($fields['month'][$month]) || !$dayMatches) {
                $cursor = $cursor->setTime(0, 0)->modify('+1 day');
                continue;
            }
            $h = (int)$cursor->format('G');
            if (!isset($fields['hour'][$h])) {
                $cursor = $cursor->setTime($h, 0)->modify('+1 hour');
                continue;
            }
            $minute = (int)$cursor->format('i');
            if (isset($fields['minute'][$minute])) { $result[] = $cursor->format('Y-m-d H:i:s'); }
            $cursor = $cursor->modify('+1 minute');
        }
        if (count($result) !== $count) { throw new InvalidArgumentException('No Cron occurrence within five years'); }
        return $result;
    }
}
