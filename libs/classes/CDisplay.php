<?php namespace mfe;

class CDisplay extends CCore implements IComponent {
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

    static public function registerComponent(){
        mfe::registerCoreComponent('display', [get_called_class(), 'display']);
        return true;
    }
}

//TODO:: normal autoload for libs, with auto registration
CDisplay::registerComponent();
