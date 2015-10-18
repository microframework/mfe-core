<?php namespace mfe\core\libs\configs;

use ArrayObject;
use mfe\core\api\configs\IConfig;
use mfe\core\libs\components\CException;

/**
 * Class CServerConfig
 *
 * @package mfe\core\libs\configs
 */
class CServerConfig extends ArrayObject
{
    const STRING_LENGTH = 2048;

    /** @var ConfigBlock */
    private $currentBlock;

    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
        $this->currentBlock = new ConfigBlock();
    }

    /**
     * @param string $file
     *
     * @return IConfig
     * @throws CException
     */
    public static function fromFile($file)
    {
        $config = new static();
        if (is_string($file) && file_exists($file) && is_readable($file) && !is_dir($file)) {
            $config->parseConfig($file);
            $config->close();
        } else {
            throw new CException('Invalid file config to parse.');
        }
        return $config->getBlockConfig();
    }

    /**
     * @param $file
     *
     * @throws CException
     */
    public function parseConfig($file)
    {
        $file = fopen($file, 'r');
        $count = 1;

        while (($line = stream_get_line($file, static::STRING_LENGTH, PHP_EOL)) !== false) {
            $line = trim($line);

            switch (substr($line, -1, 1)) {
                case '':
                case '#':
                    break;
                case ';':
                    $this->parseCommandValue($line);
                    break;
                case '{':
                    $this->openCommandBlock($line);
                    break;
                case ',':
                case '}':
                    $this->tryCloseCommandBlock($line, $count);
                    break;
                default:
                    throw new CException('Unknown line config: ' . $count . '.');
            }
            $count++;
        }
        if (!feof($file)) {
            throw new CException('Unexpected ending file.');
        }
        fclose($file);
    }

    /**
     * @param string $line
     */
    private function parseCommandValue($line)
    {
        $command = explode(':', $line, 2);

        $value = preg_replace_callback(
            '#\{\%([a-z0-9\-_\#]*?)\%\}#Si',
            function ($match) {
                return $this->getMfEConstant(strtoupper(substr($match[0], 2, -2)));
            }, substr(trim($command[1]), 0, -1));

        $this->writeBlockConfig(trim($command[0]), $value);
    }

    public function getMfEConstant($name)
    {
        if (defined('MFE_' . $name)) {
            return constant('MFE_' . $name);
        }
        return '{%' . $name . '%}';
    }

    /**
     * @param string $line
     */
    private function openCommandBlock($line)
    {
        $name = explode(' ', $line, 2)[0];

        $this->currentBlock->$name = new ConfigBlock($this->currentBlock);
        $this->currentBlock = $this->currentBlock->$name;
    }

    /**
     * @param string $line
     * @param $count
     *
     * @throws CException
     */
    private function tryCloseCommandBlock($line, $count)
    {
        $line = str_replace(' ', '', $line);
        if ('},' === $line || '}' === $line) {
            $this->currentBlock->close();
        } else {
            throw new CException('Unknown line config: ' . $count . '.');
        }

        $this->currentBlock = $this->currentBlock->getParent();
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function writeBlockConfig($key, $value)
    {
        $this->currentBlock->$key = $value;
    }

    private function close()
    {
        $this->currentBlock->close();
    }

    private function getBlockConfig()
    {
        if ($this->currentBlock->isClose()) {
            return $this->currentBlock;
        }
        return null;
    }
}

/**
 * private Class ConfigBlock
 *
 * @package mfe\core\libs\configs
 */
class ConfigBlock extends ArrayObject implements IConfig
{
    private $parent;
    private $isClosed = false;

    public function __construct($parent = null)
    {
        parent::__construct([], ArrayObject::ARRAY_AS_PROPS);
        $this->parent = $parent;
    }

    public function __set($key, ConfigBlock $value)
    {
        if ($this->isClosed) {
            throw new CException('Config block already closed.');
        }
        $this->$key = $value;
    }

    public function __toString()
    {
        return (string)$this->name;
    }

    public function close()
    {
        if (!$this->isClosed) {
            return $this->isClosed = true;
        }
        throw new CException('Config block already closed.');
    }

    public function getParent()
    {
        if (null === $this->parent) {
            return $this;
        }
        return $this->parent;
    }

    public function isClose()
    {
        if ($this->isClosed) {
            return true;
        }
        return false;
    }
}
