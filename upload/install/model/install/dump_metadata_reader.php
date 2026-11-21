<?php
/**
 * Reads descriptive SQL dump headers without loading or executing SQL.
 * Supports NovaCart metadata v1 and the legacy "-- Export DB" header.
 */
class DumpMetadataReader {
    public function read($file) {
        $info = array(
            'title' => '',
            'description' => '',
            'store' => '',
            'created' => '',
            'mode' => '',
            'tables' => '',
            'data_tables' => '',
            'batch_size' => '',
            'db_prefix' => '',
            'charset' => '',
            'collation' => '',
            'include_drop' => '',
            'format' => 'unknown',
            'size' => is_file($file) ? (int)filesize($file) : 0
        );

        if (!is_file($file) || !is_readable($file)) {
            return $info;
        }

        $handle = @fopen($file, 'rb');
        if (!$handle) {
            return $info;
        }

        $map = array(
            'title' => 'title', 'description' => 'description',
            'store' => 'store', 'created' => 'created',
            'mode' => 'mode', 'tables' => 'tables',
            'data-tables' => 'data_tables', 'batch-size' => 'batch_size',
            'db-prefix' => 'db_prefix', 'charset' => 'charset',
            'collation' => 'collation', 'include-drop' => 'include_drop'
        );
        $inMetadata = false;
        $bytes = 0;
        $lines = 0;
        $legacy = array();

        while (!feof($handle) && $bytes < 16384 && $lines < 100) {
            $line = fgets($handle, min(2048, 16384 - $bytes + 1));
            if ($line === false) {
                break;
            }
            $bytes += strlen($line);
            $lines++;
            $line = trim($line);
            if ($line === '' || strpos($line, '--') !== 0) {
                if ($line !== '' && !$inMetadata) {
                    break;
                }
                continue;
            }
            $comment = trim(substr($line, 2));
            if ($comment === 'NovaCart Dump Metadata v1') {
                $inMetadata = true;
                $info['format'] = 'novacart-v1';
                continue;
            }
            if ($comment === 'End NovaCart Dump Metadata') {
                $inMetadata = false;
                break;
            }
            if ($inMetadata) {
                if (preg_match('/^([A-Za-z-]+):\s*(.*)$/u', $comment, $match)) {
                    $key = strtolower($match[1]);
                    if (isset($map[$key])) {
                        $info[$map[$key]] = mb_substr(trim($match[2]), 0, 500, 'UTF-8');
                    }
                }
                continue;
            }
            if ($comment === 'Export DB') {
                $info['format'] = 'legacy';
            } elseif (stripos($comment, 'Created at:') === 0) {
                $info['created'] = trim(substr($comment, 11));
            } elseif (strpos($comment, 'FOR INSTALLER') === 0) {
                $info['mode'] = 'installer';
            } elseif (stripos($comment, 'Database collation:') === 0) {
                $info['collation'] = trim(substr($comment, 19));
            } elseif ($comment !== '' && strpos($comment, '---') !== 0 && count($legacy) < 2) {
                $legacy[] = $comment;
            }
        }
        fclose($handle);

        if ($info['format'] === 'legacy' && !$info['title']) {
            foreach ($legacy as $candidate) {
                if ($candidate !== 'Export DB') {
                    $info['title'] = mb_substr($candidate, 0, 200, 'UTF-8');
                    break;
                }
            }
        }
        return $info;
    }
}
