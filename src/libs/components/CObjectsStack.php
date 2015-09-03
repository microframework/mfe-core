<?php namespace mfe\core\libs\components;

use mfe\core\api\stack\IObjectsStack;
use mfe\core\libs\system\TSystemComponent;

/**
 * Class CObjectsStack
 *
 * @package mfe\core\libs\components
 */
class CObjectsStack extends \ArrayObject implements IObjectsStack
{
    use TSystemComponent;

    /** @var string */
    private $sid;

    /** @var array */
    private $objectStack = [];

    /** @var integer */
    private $index = 0;

    /** @var integer */
    protected $limit = 4096;

    /** @var integer */
    protected $min_limit = 16;

    public function __construct($array = [], $sid = null)
    {
        parent::__construct();
        if (null !== $sid) {
            $this->sid = md5($sid);
        }
        if (is_array($array) && [] !== $array) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function setLimit($new_limit = 4096)
    {
        if ($new_limit < $this->min_limit) {
            $new_limit = $this->min_limit;
        }
        $this->limit = $new_limit;
    }

    public function setIndex($new_index)
    {
        if ($new_index < 0) {
            $new_index = 1;
        }
        $this->index = $new_index;
    }

    public function __get($key)
    {
        return $this->objectStack[$key];
    }

    public function __set($key, $value)
    {
        if ($this->index > $this->limit) {
            throw new CException("The limit({$this->limit}) in a stack is reached");
        }
        if (in_array($value, parent::getArrayCopy(), true)) {
            return;
        }
        parent::offsetSet($this->index, $value);
        $this->objectStack[$key] = $value;
        $this->index++;
    }

    public function add($key, $value)
    {
        if ($this->index > $this->limit) {
            throw new CException("The limit({$this->limit}) in a stack is reached");
        }
        if (in_array($value, parent::getArrayCopy(), true)) {
            return $this;
        }
        parent::offsetSet($this->index, $value);
        $this->objectStack[$key] = $value;
        $this->index++;
        return $this;
    }

    public function remove($key, $reorder = true)
    {
        $offset = array_search($this->objectStack[$key], parent::getArrayCopy(), true);
        parent::offsetUnset($offset);
        $this->objectStack[$key] = null;
        if ($reorder) {
            $this->reorder();
        }
        return $this;
    }

    public function reorder()
    {
        $this->index = 0;
        foreach (parent::getArrayCopy() as $key => $value) {
            if (null !== $value) {
                parent::offsetUnset($key);
            }
        }
        foreach ($this->objectStack as $key => $value) {
            if (null !== $value) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @param bool|integer $to_position
     *
     * @return $this|mixed
     */
    public function position($key, $to_position = false)
    {
        $position = array_search($this->objectStack[$key], parent::getArrayCopy(), true);
        if (!$to_position) {
            return $position;
        }
        if ($to_position < 0) {
            $to_position = 0;
        }
        if ($to_position > $this->limit) {
            $to_position = $this->limit;
        }
        if ($position === $to_position) {
            return $this;
        }
        $value = (parent::offsetExists($to_position)) ? parent::offsetGet($to_position) : null;
        parent::offsetSet($to_position, parent::offsetGet($position));
        if (null !== $value) {
            parent::offsetSet($position, $value);
        } else {
            parent::offsetUnset($position);
        }
        $this->save_reposition();
        return $this;
    }

    public function up($key, $count_steps = 1, $reorder = true)
    {
        $position = array_search($this->objectStack[$key], parent::getArrayCopy(), true);
        $new_position = $position + $count_steps;
        if ($position + $count_steps < 0) {
            $new_position = 0;
        }
        if ($position + $count_steps > $this->limit) {
            $new_position = $this->limit;
        }
        if ($position === $new_position) {
            return $this;
        }

        $this->re_sort($key, $position, $new_position);
        $this->save_reposition();
        return $this;
    }

    public function down($key, $count_steps = 1)
    {
        $position = array_search($this->objectStack[$key], parent::getArrayCopy(), true);
        $new_position = $position - $count_steps;
        if ($position - $count_steps < 0) {
            $new_position = 0;
        }
        if ($position - $count_steps > $this->limit) {
            $new_position = $this->limit;
        }
        if ($position === $new_position) {
            return $this;
        }

        $this->re_sort($key, $position, $new_position);
        $this->save_reposition();
        return $this;
    }

    protected function re_sort($key, $position, $new_position)
    {
        $temp_value = $this->objectStack[$key];
        $this->remove($position, false);

        $inserted = false;
        $this->index = 0;
        foreach (parent::getArrayCopy() as $offset => $value) {
            if (null !== $value) {
                parent::offsetUnset($offset);
            }
        }
        foreach ($this->objectStack as $offset => $value) {
            if ($this->index === $new_position) {
                $this->$key = $temp_value;
                $inserted = true;
            }
            if (null !== $value) {
                $this->{$offset} = $value;
            }
        }
        if (!$inserted) {
            $this->$key = $temp_value;
        }
    }

    public function flush()
    {
        foreach ($this->objectStack as $key => $value) {
            $this->remove($key);
        }
        return $this;
    }

    protected function save_reposition()
    {
        $copy_stack = parent::getArrayCopy();
        $copy_array = $this->objectStack;

        $array = [];
        foreach ($copy_stack as $key => $value) {
            if (null !== $value) {
                $array[array_search($value, $copy_array, true)] = $value;
            }
        }

        $this->flush();
        foreach ($array as $key => $value) {
            if (null !== $value) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }
}
