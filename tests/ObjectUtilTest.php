<?php

use GoferUtil\ArrayUtil;
use GoferUtil\ObjectUtil;

class ObjectUtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider isEmptyProvider
     * @param $object
     * @param $expected
     */
    public function test_isEmpty($object, $expected) {
        $result = ObjectUtil::isEmpty($object);
        $this->assertEquals($expected, $result);
    }

    public function isEmptyProvider() {
        $object = new \stdClass();
        $object->property1 = true;
        $emptyObject = new \stdClass();
        return [
            [null, true],
            [false, true],
            [['array'], true],
            [new \stdClass(), true],
            [$object, false],
            [$emptyObject, true],
        ];
    }

    /**
     * @dataProvider doesClassExistProvider
     * @param $className
     * @param $expected
     */
	public function test_doesClassExist($className, $expected) {
		$result = ObjectUtil::doesClassExist($className);
        $this->assertEquals($expected, $result);
	}

    public function doesClassExistProvider() {
        return [
            [ArrayUtil::class, true],
            ['\\Gofer\\SDK\\Models\\Entities\\FakeClass', false],
        ];
    }

}