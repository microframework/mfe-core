<?php namespace mfe\core\libs\interfaces;
/**
 * Interface ILoader
 *
 * This Interface dictates coding rules for loader of MFE
 * Этот интерфейс диктует правила написания Загружчика для MFE
 *
 * @standards MFS-1, MFS-2, MFS-4, MFS-5.3, MFS-6
 * @package mfe\core\libs\interfaces
 */
interface ILoader
{
    function load($file, $EXT = '.php');

    function registerAliasDirectory($alias, $dir);

    function aliasDirectoryExist($alias);

    function getRealPaths($path);

    static function loadFile($file, $EXT = '.php');

    static function loadCore($file);

    static function loadPhar($file);

    static function loadMapFile($file);

    static function loadMap($mapName);

    static function map($catalog, $index, $file);
}
