<?php namespace mfe;

class CmfeObjectsStack extends \ArrayObject implements ImfeObjectsStack {
    private $objectStack = [];
    private $index = 0;
    protected $limit = 4096;
    protected $min_limit = 16;

    public function setLimit($new_limit = 4096){
        if($new_limit < $this->min_limit) $new_limit = $this->min_limit;
        $this->limit = $new_limit;
    }

    public function setIndex($new_index) {
        if($new_index < 0) $new_index = 1;
        $this->index = $new_index;
    }

    public function __get($key){
        return parent::offsetGet($key);
    }

    public function __set($key, $value){
        if($this->index > $this->limit)
            throw new \Exception("The limit({$this->limit}) in a stack is reached");
        if(array_search($value, parent::getArrayCopy())) return;
        parent::offsetSet($this->index, $value);
        $this->objectStack[$key] = $value;
        $this->index++;
    }

    public function add($key, $value){
        if($this->index > $this->limit)
            throw new \Exception("The limit({$this->limit}) in a stack is reached");
        if(array_search($value, parent::getArrayCopy())) return $this;
        parent::offsetSet($this->index, $value);
        $this->objectStack[$key] = $value;
        $this->index++;
        return $this;
    }

    public function remove($key, $reorder = true){
        $offset = array_search($this->objectStack[$key], parent::getArrayCopy());
        parent::offsetUnset($offset);
        unset($this->objectStack[$key]);
        if($reorder) $this->reorder();
        return $this;
    }

    public function reorder(){
        $this->index = 0;
        foreach(parent::getArrayCopy() as $key => $value){
            parent::offsetUnset($key);
        }
        foreach($this->objectStack as $key => $value){
            $this->$key = $value;
        }
        return $this;
    }

    public function position($key, $to_position = false){
        $position = array_search($this->objectStack[$key], parent::getArrayCopy());
        if(!$to_position){
            return $position;
        }
        if($to_position < 0) $to_position = 0;
        if($to_position > $this->limit) $to_position = $this->limit;
        if($position == $to_position) return $this;
        $value = (parent::offsetExists($to_position)) ? parent::offsetGet($to_position) : null;
        parent::offsetSet($to_position, parent::offsetGet($position));
        if(!is_null($value)) {
            parent::offsetSet($position, $value);
        } else {
            parent::offsetUnset($position);
        }
        $this->save_reposition();
        return $this;
    }

    public function up($key, $count_steps = 1, $reorder = true){
        $position = array_search($this->objectStack[$key], parent::getArrayCopy());
        $new_position = $position + $count_steps;
        if($position + $count_steps < 0) $new_position = 0;
        if($position + $count_steps > $this->limit) $new_position = $this->limit;
        if($position == $new_position) return $this;

        $temp_value = $this->objectStack[$key];
        $this->remove($position, false);

        $inserted = false;
        $this->index = 0;
        foreach(parent::getArrayCopy() as $offset => $value){
            parent::offsetUnset($offset);
        }
        foreach($this->objectStack as $offset => $value){
            if($this->index == $new_position){
                $this->$key = $temp_value;
                $inserted = true;
            }
            $this->{$offset} = $value;
        }
        if(!$inserted) $this->$key = $temp_value;
        $this->save_reposition();
        return $this;
    }

    public function down($key, $count_steps = 1){
        $position = array_search($this->objectStack[$key], parent::getArrayCopy());
        $new_position = $position - $count_steps;
        if($position - $count_steps < 0) $new_position = 0;
        if($position - $count_steps > $this->limit) $new_position = $this->limit;
        if($position == $new_position) return $this;

        $temp_value = $this->objectStack[$key];
        $this->remove($position, false);

        $inserted = false;
        $this->index = 0;
        foreach(parent::getArrayCopy() as $offset => $value){
            parent::offsetUnset($offset);
        }
        foreach($this->objectStack as $offset => $value){
            if($this->index == $new_position){
                $this->$key = $temp_value;
                $inserted = true;
            }
            $this->{$offset} = $value;
        }
        if(!$inserted) $this->$key = $temp_value;
        $this->save_reposition();
        return $this;
    }

    public function flush(){
        foreach($this->objectStack as $key => $value){
            $this->remove($key);
        }
        return $this;
    }

    protected function save_reposition(){
        $copy_stack = parent::getArrayCopy();
        $copy_array = $this->objectStack;

        $array = [];
        foreach($copy_stack as $key => $value){
            $array[array_search($value, $copy_array)] = $value;
        }

        $this->flush();
        foreach($array as $key => $value){
            $this->{$key} = $value;
        }
        return $this;
    }
}
