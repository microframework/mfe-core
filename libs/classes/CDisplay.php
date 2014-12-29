<?php namespace mfe;

/**
 * Class CDisplay
 *
 * @package mfe
 */
class CDisplay extends CCore implements IComponent {
    /** @var CDisplay */
    static private $instance;

    /**
     * @return CDisplay
     */
    static public function getInstance(){
        /** @var CDisplay $class */
        $class = get_called_class();

        if (is_null($class::$instance)) {
            $class::$instance = new $class();
        }
        return (object)$class::$instance;
    }

    /**
     * @param $data
     */
    static public function display($data){
        print $data;
    }

    /**
     * @return bool
     */
    static public function registerComponent(){
        mfe::registerCoreComponent('display', [get_called_class(), 'display']);
        return true;
    }
}

//TODO:: normal autoload for libs, with auto registration
CDisplay::registerComponent();
