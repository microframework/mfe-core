<?php namespace mfe;

//TODO:: сделать нормальный автолоад
spl_autoload_register(function ($file) {
    if (substr($file, 0, 3) !== 'mfe' && substr($file, 5, 3) !== 'mfe') return false;
    $index = substr($file, 0, 5);
    $file = substr($file, 3);
    switch ($index) {
        case 'mfe\C':
            /** @noinspection PhpIncludeInspection */
            return include_once str_replace(['\/', '//', '\\\\', '/\\'], '/', 'classes/' . $file . '.php');
            break;
        case 'mfe\I':
            /** @noinspection PhpIncludeInspection */
            return include_once str_replace(['\/', '//', '\\\\', '/\\'], '/', 'interfaces/' . $file . '.php');
            break;
        case 'mfe\T':
            /** @noinspection PhpIncludeInspection */
            return include_once str_replace(['\/', '//', '\\\\', '/\\'], '/', 'traits/' . $file . '.php');
            break;
        default:
            return false;
    }
}, false, true);