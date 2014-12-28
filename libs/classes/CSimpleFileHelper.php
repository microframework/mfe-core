<?php namespace mfe;
/**
 * Simple File Helper
 */
if (!class_exists('mfe\CSimpleFileHelper')) {
    class CSimpleFileHelper {
        static public $SEPARATOR = '/';
        static public $PHP = '.php';
        static public $Phar = '.phar';

        final static public function scandir_recursive($dir, $trim = null) {
            $result = [];
            if (is_null($trim)) $trim = strlen($dir);
            if (file_exists($dir) && is_dir($dir)) {
                $path = scandir($dir);
                foreach ($path as $fileInfo) {
                    $file = $dir . self::$SEPARATOR . $fileInfo;
                    if (is_dir($file) && $fileInfo != '.' && $fileInfo != '..') {
                        $result = array_merge($result, self::scandir_recursive($file, $trim));
                    }
                    if (is_file($file) && $fileInfo != '.' && $fileInfo != '..') {
                        $line = substr($file, $trim);
                        if ('/' == substr($line, 0, 1) || '\\' == substr($line, 0, 1)) $line = substr($line, 1);
                        $result[] = str_replace(['//'], '/', $line);
                    }
                }
            }
            return $result;
        }
    }
}
