<?php namespace mfe\core\cores;

use PHPUnit_Framework_TestCase;

/**
 * Class PageTest
 *
 * @package mfe\core\cores
 *
 * @backupGlobals disabled
 */
class PageTest extends PHPUnit_Framework_TestCase {
    /** @var array */
    private $data;

    /** @var Page */
    private $page;

    public function setUp() {
        $this->data = [
            'test1' => 'test1',
            'test2' => 2,
            'test3' => 3
        ];

        $this->page = new Page(null, [
            'test0' => 'test0',
            'test1' => 1
        ]);
    }

    public function testAddData() {
        $data = [
            'test0' => 'test0',
            'test1' => 1
        ];

        $this->page->addData($this->data);
        static::assertEquals(array_merge($data, $this->data), $this->page->getData());
    }

    public function testSetData() {
        $this->page->setData($this->data);
        static::assertEquals($this->data, $this->page->getData());
    }
}
