<?php
/**
 * NovaCart localization audit.
 *
 * PHP 7.3+ compatible.
 *
 * Usage:
 *   php tools/i18n_audit.php
 *   php tools/i18n_audit.php --strict-coverage
 *
 * Blocking checks:
 *   - UTF-8 BOM / invalid UTF-8
 *   - duplicate language keys in one file
 *   - incompatible sprintf-style placeholders between locales
 *   - incompatible value types between locales
 *
 * Missing files/keys are warnings by default. Use --strict-coverage to make
 * coverage differences blocking after the existing translation debt is fixed.
 */

$root = dirname(__DIR__);
$locales = array('en-gb', 'ru-ru', 'uk-ua');
$areas = array(
    'admin'   => $root . '/upload/admin/language',
    'catalog' => $root . '/upload/catalog/language',
    'install' => $root . '/upload/install/language',
);
$strictCoverage = in_array('--strict-coverage', $argv, true);
$isGithub = getenv('GITHUB_ACTIONS') === 'true';

$errors = 0;
$warnings = 0;
$stats = array(
    'files' => 0,
    'keys' => 0,
    'format_checks' => 0,
    'coverage_warnings' => 0,
);

function auditMessage($level, $message, $file = '', $line = 0) {
    global $isGithub, $errors, $warnings;

    if ($level === 'error') {
        $errors++;
    } else {
        $warnings++;
    }

    if ($isGithub) {
        $annotation = $level === 'error' ? 'error' : 'warning';
        $meta = '';

        if ($file !== '') {
            $meta = ' file=' . str_replace(',', '%2C', $file);

            if ($line > 0) {
                $meta .= ',line=' . (int)$line;
            }
        }

        $safe = str_replace(
            array('%', "\r", "\n"),
            array('%25', '%0D', '%0A'),
            $message
        );

        echo '::' . $annotation . $meta . '::' . $safe . PHP_EOL;
        return;
    }

    $prefix = strtoupper($level);

    if ($file !== '') {
        $prefix .= ' [' . $file;

        if ($line > 0) {
            $prefix .= ':' . (int)$line;
        }

        $prefix .= ']';
    }

    echo $prefix . ': ' . $message . PHP_EOL;
}

function auditRelativePath($path, $root) {
    $path = str_replace('\\', '/', $path);
    $root = rtrim(str_replace('\\', '/', $root), '/') . '/';

    return strpos($path, $root) === 0 ? substr($path, strlen($root)) : $path;
}

function auditListPhpFiles($base) {
    $result = array();

    if (!is_dir($base)) {
        return $result;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $baseNormalized = rtrim(str_replace('\\', '/', $base), '/') . '/';
        $pathNormalized = str_replace('\\', '/', $file->getPathname());
        $relative = substr($pathNormalized, strlen($baseNormalized));

        $result[$relative] = $file->getPathname();
    }

    ksort($result);

    return $result;
}

function auditTokenText($token) {
    return is_array($token) ? $token[1] : $token;
}

function auditTokenLine($token) {
    return is_array($token) && isset($token[2]) ? (int)$token[2] : 0;
}

function auditIgnorable($token) {
    return is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true);
}

function auditNextSignificant($tokens, $index) {
    $count = count($tokens);

    while ($index < $count && auditIgnorable($tokens[$index])) {
        $index++;
    }

    return $index;
}

function auditDecodeQuoted($literal) {
    $literal = (string)$literal;

    if (strlen($literal) < 2) {
        return $literal;
    }

    $quote = $literal[0];
    $body = substr($literal, 1, -1);

    if ($quote === "'") {
        return str_replace(
            array("\\\\", "\\'"),
            array("\\", "'"),
            $body
        );
    }

    return stripcslashes($body);
}

function auditExpressionType($tokens) {
    foreach ($tokens as $token) {
        if (auditIgnorable($token)) {
            continue;
        }

        if (is_array($token)) {
            if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                return 'string';
            }

            if ($token[0] === T_ARRAY) {
                return 'array';
            }

            if ($token[0] === T_LNUMBER) {
                return 'integer';
            }

            if ($token[0] === T_DNUMBER) {
                return 'float';
            }

            if ($token[0] === T_STRING) {
                $value = strtolower($token[1]);

                if ($value === 'true' || $value === 'false') {
                    return 'boolean';
                }

                if ($value === 'null') {
                    return 'null';
                }
            }

            return 'expression';
        }

        if ($token === '[') {
            return 'array';
        }

        return 'expression';
    }

    return 'unknown';
}

function auditExpressionRaw($tokens) {
    $raw = '';

    foreach ($tokens as $token) {
        $raw .= auditTokenText($token);
    }

    return $raw;
}

function auditFormatSignature($raw) {
    $matches = array();

    preg_match_all(
        "/%(?:\\d+\\$)?[-+0 ]*(?:\\d+)?(?:\\.\\d+)?[bcdeEfFgGosuxX%]/",
        $raw,
        $matches
    );

    $result = isset($matches[0]) ? $matches[0] : array();
    sort($result);

    return $result;
}

function auditParseLanguageFile($file, $display) {
    $content = @file_get_contents($file);

    if ($content === false) {
        auditMessage('error', 'Unable to read language file.', $display);
        return array('keys' => array());
    }

    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        auditMessage('error', 'UTF-8 BOM is not allowed.', $display, 1);
    }

    if (!preg_match('//u', $content)) {
        auditMessage('error', 'File is not valid UTF-8.', $display, 1);
    }

    $tokens = token_get_all($content);
    $count = count($tokens);
    $keys = array();
    $duplicateLines = array();

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (!is_array($token) || $token[0] !== T_VARIABLE || $token[1] !== '$_') {
            continue;
        }

        $line = auditTokenLine($token);
        $j = auditNextSignificant($tokens, $i + 1);

        if ($j >= $count || auditTokenText($tokens[$j]) !== '[') {
            continue;
        }

        $j = auditNextSignificant($tokens, $j + 1);

        if (
            $j >= $count ||
            !is_array($tokens[$j]) ||
            $tokens[$j][0] !== T_CONSTANT_ENCAPSED_STRING
        ) {
            continue;
        }

        $key = auditDecodeQuoted($tokens[$j][1]);
        $j = auditNextSignificant($tokens, $j + 1);

        if ($j >= $count || auditTokenText($tokens[$j]) !== ']') {
            continue;
        }

        $j = auditNextSignificant($tokens, $j + 1);

        if ($j >= $count || auditTokenText($tokens[$j]) !== '=') {
            continue;
        }

        $j++;
        $expr = array();
        $round = 0;
        $square = 0;
        $curly = 0;

        for (; $j < $count; $j++) {
            $part = $tokens[$j];
            $text = auditTokenText($part);

            if ($text === '(') {
                $round++;
            } elseif ($text === ')') {
                $round = max(0, $round - 1);
            } elseif ($text === '[') {
                $square++;
            } elseif ($text === ']') {
                $square = max(0, $square - 1);
            } elseif ($text === '{') {
                $curly++;
            } elseif ($text === '}') {
                $curly = max(0, $curly - 1);
            }

            if ($text === ';' && $round === 0 && $square === 0 && $curly === 0) {
                break;
            }

            $expr[] = $part;
        }

        $entry = array(
            'line' => $line,
            'type' => auditExpressionType($expr),
            'format' => auditFormatSignature(auditExpressionRaw($expr)),
        );

        if (isset($keys[$key])) {
            if (!isset($duplicateLines[$key])) {
                $duplicateLines[$key] = array($keys[$key]['line']);
            }

            $duplicateLines[$key][] = $line;
        }

        $keys[$key] = $entry;
        $i = $j;
    }

    foreach ($duplicateLines as $key => $lines) {
        auditMessage(
            'error',
            'Duplicate language key "' . $key . '" on lines ' . implode(', ', $lines) . '.',
            $display,
            $lines[0]
        );
    }

    return array('keys' => $keys);
}

foreach ($areas as $area => $languageRoot) {
    echo PHP_EOL . '== ' . strtoupper($area) . ' ==' . PHP_EOL;

    $localeFiles = array();
    $allRelativeFiles = array();

    foreach ($locales as $locale) {
        $localeBase = $languageRoot . '/' . $locale;
        $localeFiles[$locale] = auditListPhpFiles($localeBase);

        foreach ($localeFiles[$locale] as $relative => $absolute) {
            $allRelativeFiles[$relative] = true;
        }
    }

    ksort($allRelativeFiles);

    foreach (array_keys($allRelativeFiles) as $relative) {
        $parsed = array();
        $present = array();

        foreach ($locales as $locale) {
            if (!isset($localeFiles[$locale][$relative])) {
                $stats['coverage_warnings']++;
                auditMessage(
                    $strictCoverage ? 'error' : 'warning',
                    'Missing translation file for ' . $locale . ': ' . $area . '/' . $relative
                );
                continue;
            }

            $display = auditRelativePath($localeFiles[$locale][$relative], $root);
            $parsed[$locale] = auditParseLanguageFile($localeFiles[$locale][$relative], $display);
            $present[] = $locale;
            $stats['files']++;
            $stats['keys'] += count($parsed[$locale]['keys']);
        }

        if (count($present) < 2) {
            continue;
        }

        $allKeys = array();

        foreach ($present as $locale) {
            foreach ($parsed[$locale]['keys'] as $key => $entry) {
                $allKeys[$key] = true;
            }
        }

        ksort($allKeys);

        foreach (array_keys($allKeys) as $key) {
            $entries = array();

            foreach ($locales as $locale) {
                if (!isset($parsed[$locale])) {
                    continue;
                }

                if (!isset($parsed[$locale]['keys'][$key])) {
                    $stats['coverage_warnings']++;
                    auditMessage(
                        $strictCoverage ? 'error' : 'warning',
                        'Missing key "' . $key . '" for ' . $locale . ' in ' . $area . '/' . $relative
                    );
                    continue;
                }

                $entries[$locale] = $parsed[$locale]['keys'][$key];
            }

            if (count($entries) < 2) {
                continue;
            }

            $referenceLocale = isset($entries['en-gb']) ? 'en-gb' : array_keys($entries)[0];
            $reference = $entries[$referenceLocale];

            foreach ($entries as $locale => $entry) {
                if ($locale === $referenceLocale) {
                    continue;
                }

                if ($entry['type'] !== $reference['type']) {
                    auditMessage(
                        'error',
                        'Type mismatch for "' . $key . '": ' .
                        $referenceLocale . '=' . $reference['type'] . ', ' .
                        $locale . '=' . $entry['type'] . '.',
                        $area . '/language/' . $locale . '/' . $relative,
                        $entry['line']
                    );
                }

                if ($reference['type'] === 'string' && $entry['type'] === 'string') {
                    $stats['format_checks']++;

                    if ($entry['format'] !== $reference['format']) {
                        auditMessage(
                            'error',
                            'Format placeholder mismatch for "' . $key . '": ' .
                            $referenceLocale . ' [' . implode(', ', $reference['format']) . '] vs ' .
                            $locale . ' [' . implode(', ', $entry['format']) . '].',
                            $area . '/language/' . $locale . '/' . $relative,
                            $entry['line']
                        );
                    }
                }
            }
        }
    }
}

echo PHP_EOL . 'Localization audit summary' . PHP_EOL;
echo '  Files parsed:       ' . $stats['files'] . PHP_EOL;
echo '  Keys parsed:        ' . $stats['keys'] . PHP_EOL;
echo '  Format comparisons: ' . $stats['format_checks'] . PHP_EOL;
echo '  Coverage warnings:  ' . $stats['coverage_warnings'] . PHP_EOL;
echo '  Errors:             ' . $errors . PHP_EOL;
echo '  Warnings:           ' . $warnings . PHP_EOL;

if (!$strictCoverage && $stats['coverage_warnings'] > 0) {
    echo '  Coverage mode:      warning-only (use --strict-coverage to enforce)' . PHP_EOL;
}

exit($errors > 0 ? 1 : 0);
