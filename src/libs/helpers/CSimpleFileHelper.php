<?php namespace mfe\core\libs\helpers;

use mfe\core\libs\traits\system\TSystemComponent;

/**
 * Class CSimpleFileHelper
 * @package mfe\core\libs\helpers
 */
class CSimpleFileHelper
{
    use TSystemComponent;

    static public $SEPARATOR = '/';
    static public $PHP = '.php';
    static public $Phar = '.phar';

    /**
     * @param $dir
     * @param null $trim
     * @return array
     */
    final static public function scandir_recursive($dir, $trim = null)
    {
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

    static public function convert_size($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[intval($i)];
    }
}
