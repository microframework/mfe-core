<?php
namespace mfe\core\libs;

/**
 * Class PSR4Autoload
 * @package mfe\core\libs
 */
class PSR4Autoload
{
    /** @var array */
    protected $prefixes = [];

    /**
     * @return void
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * @param string $prefix
     * @param string $base_dir
     * @param bool $prepend
     * @return void
     */
    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }

        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    /**
     * @param string $class
     * @return mixed
     */
    public function loadClass($class)
    {
        $prefix = $class;

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);

            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    /**
     * @param string $prefix
     * @param string $relative_class
     * @return mixed
     */
    protected function loadMappedFile($prefix, $relative_class)
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if ($this->requireFile($file)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * Если файл существует, загружеаем его.
     *
     * @param string $file файл для загрузки.
     * @return bool true если файл существует, false если нет.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            /** @noinspection PhpIncludeInspection */
            require $file;
            return true;
        }
        return false;
    }
}
