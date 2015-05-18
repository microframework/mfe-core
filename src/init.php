<?php namespace mfe\core;

/**
 * Class Init
 *
 * This class gives opportunity to configure system even before its start,
 * by means of the re-recorded configuration multi-level recursively.
 *
 * @package mfe\core
 */
final class Init
{
    const DIR_TYPE_ROOT = 'root';
    const DIR_TYPE_MFE = 'engine';
    const DIR_TYPE_APP = 'app';
    const DIR_TYPE_DATA = 'data';

    static private $DIR_PRIORITY = [
        self::DIR_TYPE_MFE => [],
        self::DIR_TYPE_APP => [],
        self::DIR_TYPE_DATA => [],
        self::DIR_TYPE_ROOT => []
    ];

    static public $SEPARATOR = '/';
    static public $configFile = 'mfe.config.php';

    private $dirs = [];
    private $config = [];

    /**
     * Start construct the Init
     */
    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', true);

        $this->init();
        $this->scanFoldersForConfigs();
        $this->overrideConfigWithConstant();
    }

    /**
     * Invoke method to return config
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->config;
    }

    /**
     * Initialize priority dirs to array of paths
     */
    private function init()
    {
        foreach (self::$DIR_PRIORITY as $type) {
            foreach ($type as $hash => $dir) {
                $this->dirs[$hash] = $dir;
            }
        }
    }

    /**
     * Add path to configs
     *
     * @param $dir
     * @param string $type
     * @return string
     */
    static public function addConfigPath($dir, $type = self::DIR_TYPE_DATA)
    {
        if (file_exists($dir) && is_dir($dir) && is_readable($dir)) {
            return false;
        }

        $hash = md5((string)$dir);
        self::$DIR_PRIORITY[$type][$hash] = $dir;

        return $hash;
    }

    /**
     * Remove path to configs
     *
     * @param $hash
     * @param string $type
     * @return bool
     */
    static public function removeConfigPath($hash, $type = self::DIR_TYPE_DATA)
    {
        if (isset(self::$DIR_PRIORITY[$type][$hash])) {
            self::$DIR_PRIORITY[$type][$hash] = null;
            return true;
        }
        return false;
    }

    /**
     * Register all MFE_* constant to options in config
     */
    private function overrideConfigWithConstant()
    {
        $constants = get_defined_constants(true)['user'];
        if ($constants) {
            foreach ($constants as $constant => $value) {
                if (substr($constant, 0, 4) === 'MFE_') {
                    $key = strtolower(substr($constant, 4));
                    if (isset($this->config['options'][$key])) {
                        $this->config['options'][$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Find all configs in dirs
     */
    private function scanFoldersForConfigs()
    {
        foreach ($this->dirs as $dir) {
            $file = $dir . self::$SEPARATOR . self::$configFile;

            if (file_exists($file) && is_readable($file) && !is_dir($file)) {
                $this->loadConfigAndMerge($file);
            }
        }
    }

    /**
     * Load config with recursive merging previous
     *
     * @param $file
     * @return string
     */
    private function loadConfigAndMerge($file)
    {
        /** @noinspection PhpIncludeInspection */
        $config = include($file);
        $this->config = array_merge_recursive($this->config, $config);
    }
}
