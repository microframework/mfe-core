<?php namespace mfe;

class CmfeDisplay {
    static private $instance;

    /**
     * @return CmfeDisplay
     */
    static public function init(){
        $class = get_called_class();
        /** @var CmfeDisplay $class */
        if (is_null($class::$instance)) {
            $class::$instance = new $class();
        }
        return (object)$class::$instance;
    }

    static public function display($data){
        print $data;
    }
}

mfe::registerCoreComponent('display', ['mfe\CmfeDisplay', 'display']);
