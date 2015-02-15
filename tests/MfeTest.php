<?php namespace mfe;

use mfe\core\mfe;

class MfeTest extends \PHPUnit_Framework_TestCase {
    public function testMfeInterfaceInstance() {
        $this->assertInstanceOf('mfe\core\libs\interfaces\IEventsManager', mfe::app());
    }
}