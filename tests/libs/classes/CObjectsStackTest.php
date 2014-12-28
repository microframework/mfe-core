<?php namespace mfe;

class CObjectsStackTest extends \PHPUnit_Framework_TestCase {

    /** @var CmfeObjectsStack $stack */
    private $stack;

    private $enablePrintDebugTables = false;

    public function setUp() {
        $this->stack = new CObjectsStack([0, 1, 2, 3, 4]);
    }

    protected function printStackTable(CObjectsStack $stack) {
        $result = '|';
        for ($j = 0; $j < count($stack); $j++) {
            $result .= $j . '|';
        }
        $result .= PHP_EOL . '|';
        foreach ($stack as $key => $value) {
            $result .= $value . '|';
        }
        print $result . PHP_EOL;
    }

    protected function debugTable($message, CObjectsStack $stack) {
        if ($this->enablePrintDebugTables) {
            print PHP_EOL . $message . ':' . PHP_EOL;
            $this->printStackTable($stack);
        }
    }

    public function testInterfaceInstance() {
        $this->assertInstanceOf('\mfe\IObjectsStack', $this->stack,
            '');
    }

    public function testCreate() {
        $this->stack = new CObjectsStack([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestCreate', $this->stack);
    }

    public function testAdd() {
        $this->stack->add(5, 5);
        $this->stack->add(6, 6);
        $this->stack->add(7, 7);
        $this->stack->add(8, 8);
        $this->stack->add(9, 9);

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestAdd', $this->stack);
    }

    public function testRemove() {
        $this->stack->remove(0);
        $this->stack->remove(4);

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([1, 2, 3], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestRemove', $this->stack);
    }

    public function testFlush() {
        $this->stack->flush();

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestFlush', $this->stack);
    }

    public function testUp() {
        $this->stack = new CObjectsStack([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->stack->up(2, 6);

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([0, 1, 3, 4, 5, 6, 7, 8, 2, 9], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestUp', $this->stack);
    }

    public function testDown() {
        $this->stack = new CObjectsStack([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->stack->down(3, 4);

        $temp_array = [];
        foreach ($this->stack as $key => $value) {
            $temp_array[] = $value;
        }

        $this->assertEquals([3, 0, 1, 2, 4, 5, 6, 7, 8, 9], $temp_array, 'Stack not formed array when iterated');
        $this->debugTable('TestDown', $this->stack);
    }
}
