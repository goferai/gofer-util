<?php

use GoferUtil\SQL\SQLConnection;
use GoferUtil\SQL\SQLParameter;
use GoferUtil\SQL\SQLParameterList;
use GoferUtil\SQL\SQLQuery;

class SQLQueryTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider selectProvider
     * @param $sql
     * @param $class
     * @param $expectedCount
     */
	public function test_select($sql, $class, $expectedCount) {
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sql);
        if (!empty($class)) {
            $sqlQuery->setClass($class);
        }
        $sqlQuery->select();
        $results = $sqlQuery->getResults();
        $this->assertEquals($expectedCount, $results->count());
        if (!empty($class)) {
            $this->assertInstanceOf($class, $results->first());
        }
	}

    public function selectProvider() {
        return [
            ['select now()', null, 1],
        ];
    }

    /**
     * @dataProvider selectSecureProvider
     * @param $sql
     * @param $class
     * @param SQLParameterList $sqlParameterList
     * @param $expectedCount
     */
    public function test_selectSecure($sql, $class, $sqlParameterList, $expectedCount) {
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sql);
        if (!empty($class)) {
            $sqlQuery->setClass($class);
        }
        $sqlQuery->selectSecure($sqlParameterList);
        $results = $sqlQuery->getResults();
        $this->assertEquals($expectedCount, $results->count());
        if (!empty($class)) {
            $this->assertInstanceOf($class, $results->first());
        }
    }

    public function selectSecureProvider() {
        $sqlParameterList1 = new SQLParameterList();
        $sqlParameterList1->add(new SQLParameter(':s', 'some text'));
        return [
            ['select :s', null, $sqlParameterList1, 1],
        ];
    }

    /**
     * Ensure it works with an in list
     */
    public function test_selectSecure_InList() {
        $ids = [1, 2];
        $conditionString = '';
        $sqlParameterList = new SQLParameterList();
        $sqlParameterList->buildMultipleForArray(':user_id', $ids, $conditionString, PDO::PARAM_INT);
        $sql = "select * from test where id in ($conditionString)";
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $results = $sqlQuery->setQuery($sql)
                            ->setClass(null)
                            ->selectSecure($sqlParameterList)
                            ->getResults();
        $this->assertEquals(2, $results->count());
        //$this->assertInstanceOf(null, $results->first());
    }


}