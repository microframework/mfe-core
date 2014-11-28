<?php namespace mfe;

class MfeTest extends \PHPUnit_Framework_TestCase {
    public function testMfeInterfaceInstance() {
        $this->assertInstanceOf('mfe\ImfeEngine', mfe::init());
        $this->assertInstanceOf('mfe\ImfeEventsManager', mfe::init());
        $this->assertInstanceOf('mfe\ImfeLoader', mfe::init());
    }
}