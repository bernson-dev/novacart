<?php
class StorageMigration {
    public static function suggestedPath($root) {
        $root = realpath($root);
        if (!$root) {
            throw new RuntimeException('Invalid site directory');
        }
        $domain = basename($root);
        $domain = preg_replace('/[^a-zA-Z0-9._-]/', '-', $domain);
        $domain = trim($domain, '.-');
        if ($domain === '') {
            throw new RuntimeException('Invalid site directory name');
        }
        $suffix = bin2hex(random_bytes(4));
        return dirname($root) . '/storage/' . $domain . '-' . $suffix . '/';
    }
    public static function move($source, $target, $configs) {
        $source = realpath($source);
        $container = dirname(rtrim($target, '/'));
        if (!is_dir($container)) {
            $base = realpath(dirname($container));
            if (!$base || !is_writable($base) || basename($container) !== 'storage' ||
                !@mkdir($container, 0750)) {
                throw new RuntimeException('Cannot create storage container');
            }
        }
        $parent = realpath($container);
        if (!$source || !is_dir($source) || is_link($source) || !$parent || !is_writable($parent)) {
            throw new RuntimeException('Storage location is unavailable');
        }
        $target = $parent . '/' . basename(rtrim($target, '/'));
        if (strpos(str_replace('\\', '/', $target) . '/', str_replace('\\', '/', rtrim($source, '/')) . '/') === 0 || file_exists($target) || is_link($target)) {
            throw new RuntimeException('Storage destination already exists');
        }
        $siteRoot = str_replace('\\', '/', dirname(dirname($source))) . '/';
        if (strpos(str_replace('\\', '/', $target) . '/', $siteRoot) === 0) {
            throw new RuntimeException('Storage must be outside the website directory');
        }
        $old = array();
        foreach ($configs as $file) {
            if (!is_file($file) || !is_writable($file)) {
                throw new RuntimeException('Configuration file is not writable');
            }
            $old[$file] = file_get_contents($file);
            if ($old[$file] === false || strpos($old[$file], "define('DIR_STORAGE'") === false) { throw new RuntimeException('Invalid config file: ' . $file); }
        }
        if (!mkdir($target, 0750)) {
            throw new RuntimeException('Cannot create storage directory');
        }
        $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($items as $item) {
            if ($item->isLink()) { throw new RuntimeException('Storage contains symlinks'); }
            $relative = substr($item->getPathname(), strlen($source) + 1);
            $dest = $target . '/' . $relative;
            if ($item->isDir()) {
                if (!is_dir($dest) && !mkdir($dest, 0750)) { throw new RuntimeException('Cannot create storage subdirectory'); }
            } elseif (!$item->isFile() || !copy($item->getPathname(), $dest) || hash_file('sha256', $item->getPathname()) !== hash_file('sha256', $dest)) {
                throw new RuntimeException('Storage copy validation failed');
            }
        }
        foreach (array('cache','logs','download','upload','session','modification') as $dir) {
            if (!is_dir($target . '/' . $dir) && !mkdir($target . '/' . $dir, 0750)) { throw new RuntimeException('Cannot create required storage folder: ' . $dir); }
        }
        $changed = array();
        foreach ($old as $file => $body) {
            $replacement = "define('DIR_STORAGE', '" . addslashes(str_replace('\\', '/', $target) . '/') . "');";
            $new = preg_replace("~define\\s*\\(\\s*'DIR_STORAGE'\\s*,[^;]+;~", $replacement, $body, 1, $count);
            if (!$count || file_put_contents($file, $new, LOCK_EX) !== strlen($new)) {
                foreach ($changed as $path) { file_put_contents($path, $old[$path], LOCK_EX); }
                file_put_contents($file, $old[$file], LOCK_EX);
                throw new RuntimeException('Cannot update storage configuration');
            }
            $changed[] = $file;
        }
        return str_replace('\\', '/', $target) . '/';
    }
}
