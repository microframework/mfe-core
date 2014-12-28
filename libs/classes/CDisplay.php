<?php namespace mfe;

class CDisplay extends CCore {
    static private $instance;

    /**
     * @return CDisplay
     */
    static public function getInstance(){
        $class = get_called_class();
        /** @var CDisplay $class */
        if (is_null($class::$instance)) {
            $class::$instance = new $class();
        }
        return (object)$class::$instance;
    }

    static public function display($data){
        print $data;
    }
}

mfe::registerCoreComponent('display', ['mfe\CDisplay', 'display']);
