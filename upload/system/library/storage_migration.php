<?php
class StorageMigration {
    public static function suggestedPath($root) {
        $root = realpath($root);
        if (!$root) { throw new RuntimeException('Invalid site directory'); }
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', basename($root));
        return dirname($root) . '/novacart-storage-' . $slug . '/';
    }
    public static function move($source, $target, $configs) {
        $source = realpath($source);
        $parent = realpath(dirname(rtrim($target, '/')));
        if (!$source || !is_dir($source) || is_link($source) || !$parent || !is_writable($parent)) {
            throw new RuntimeException('Storage location is unavailable');
        }
        $target = $parent . '/' . basename(rtrim($target, '/'));
        if (strpos(str_replace('\\', '/', $target) . '/', str_replace('\\', '/', rtrim($source, '/')) . '/') === 0 || file_exists($target) || is_link($target)) {
            throw new RuntimeException('Storage destination already exists');
        }
        if (strpos($target, "'") !== false || strpos($target, '
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
            $replacement = "define('DIR_STORAGE', '" . str_replace('\\', '/', $target) . "/');";
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
) !== false) {
            throw new RuntimeException('Storage path contains unsupported characters');
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
            $replacement = "define('DIR_STORAGE', '" . str_replace('\\', '/', $target) . "/');";
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
