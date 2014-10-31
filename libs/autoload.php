<?php namespace mfe;

spl_autoload_register(function ($file) {
    if(substr($file, 0, 3) !== 'mfe' && substr($file, 5, 3) !== 'mfe') return false;
    $index = substr($file, 0, 8);
    $file  = substr($file, 3);
    switch($index) {
        case 'mfe\Cmfe':
            /** @noinspection PhpIncludeInspection */
            return include_once 'classes/'.$file.'.php';
            break;
        case 'mfe\Imfe':
            /** @noinspection PhpIncludeInspection */
            return include_once 'interfaces/'.$file.'.php';
            break;
        case 'mfe\Tmfe':
            /** @noinspection PhpIncludeInspection */
            return include_once 'traits/'.$file.'.php';
            break;
        default:
            return false;
    }
}, false, true);