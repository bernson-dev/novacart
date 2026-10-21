<?php
/**
 * Optional admin cold-start profiler. Enable with NOVACART_PROFILE_ADMIN=1
 * in the PHP process environment. Never accepts activation from HTTP input.
 */
final class NovaCartAdminProfiler {
    private static $start = 0.0;
    private static $marks = array();
    private static $metrics = array();

    public static function start() {
        self::$start = microtime(true);
        self::$marks['entry'] = self::$start;
        register_shutdown_function(array(__CLASS__, 'finish'));
    }

    public static function mark($name) {
        if (self::$start > 0) {
            self::$marks[$name] = microtime(true);
        }
    }

    public static function add($name, $seconds) {
        if (!isset(self::$metrics[$name])) {
            self::$metrics[$name] = array('count' => 0, 'seconds' => 0.0);
        }
        self::$metrics[$name]['count']++;
        self::$metrics[$name]['seconds'] += $seconds;
    }

    public static function finish() {
        if (!defined('DIR_LOGS') || self::$start === 0.0) {
            return;
        }

        $times = array();
        foreach (self::$marks as $name => $value) {
            $times[$name] = round(($value - self::$start) * 1000, 2);
        }
        $metrics = array();
        foreach (self::$metrics as $name => $value) {
            $metrics[$name] = array(
                'count' => $value['count'],
                'ms' => round($value['seconds'] * 1000, 2)
            );
        }
        // Only route name is logged; never include query strings, tokens, POST or cookies.
        $route = isset($_GET['route']) && is_string($_GET['route'])
            ? preg_replace('/[^a-zA-Z0-9_\/]/', '', $_GET['route'])
            : 'common/dashboard';
        $loaded = array('loader' => 'not_loaded', 'twig' => 'not_loaded', 'framework' => 'not_loaded');
        foreach (get_included_files() as $included) {
            $normalized = str_replace('\\\\', '/', $included);
            foreach (array('loader' => '/engine/loader.php', 'twig' => '/library/template/twig.php', 'framework' => '/system/framework.php') as $name => $suffix) {
                if (substr($normalized, -strlen($suffix)) === $suffix) {
                    $loaded[$name] = defined('DIR_MODIFICATION') && strpos($normalized, str_replace('\\\\', '/', rtrim(DIR_MODIFICATION, '/\\\\'))) === 0 ? 'ocmod' : 'original';
                }
            }
        }
        $record = array(
            'time' => date('c'),
            'route' => $route,
            'total_ms' => round((microtime(true) - self::$start) * 1000, 2),
            'marks_ms' => $times,
            'operations' => $metrics,
            'loaded_sources' => $loaded,
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1048576, 2)
        );
        @file_put_contents(DIR_LOGS . 'admin-cold-profile.log', json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
