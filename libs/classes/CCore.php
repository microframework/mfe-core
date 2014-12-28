<?php namespace mfe;

class CCore {
    /** @var CCore */
    static private $instance = null;

    static public function getInstance() {
        if (is_null(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    static protected function option($option) {
        return mfe::option($option);
    }
}