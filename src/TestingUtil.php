<?php

namespace GoferUtil;

use GoferUtil\SQL\SQLConnection;
use GoferUtil\SQL\SQLQuery;
use ReflectionClass;
use ReflectionMethod;

/**
 * For common testing stuff to retrieve data and set fake operations
 */
class TestingUtil {

    /**
     * Set this to some datetime object in the past to fake dates in things like mysql when using now()
     * @var \DateTime
     */
	public static $fakeCurrentDateTime = false;

    /**
     * Set to true to avoid saving salesforce events when testing
     * @var bool
     */
	public static $fakeCalendarAddSalesforceEventFlag = false;

    /**
     * @param string $sql
     */
	public static function executeSQL($sql) {
		$log = new Log(basename(__FILE__));
		$log->debug("executeSQL start - sql = ".$sql);
		$result = SQLConnection::getInstance()->exec($sql);
		$log->debug("executeSQL result = ".json_encode($result));
	}
	
	/**
	 * Run a quick query to get a single row of data. Returns false if no data is returned. If multiple it only returns the first
	 * @param string $query
	 * @param string $class Optional. Specify a class the row should be returned in otherwise stdClass is returned. Default = null
	 * @return mixed|false
	 */
	public static function getSingleRow($query, $class=null) {
		$sqlQuery = new SQLQuery(SQLConnection::getInstance());
		$sqlQuery->setQuery($query);
        if (isset($class)) {
            $sqlQuery->setClass($class);
        }
        $results = $sqlQuery->select()->getResults();
		if ($results->count() === 0) return false;
		return $results->first();
	}
	
	/**
	 * Run a quick query to get a multiple rows of data. Returns false if no data is returned.
	 * @param string $query
	 * @param string $class Optional. Specify a class the row should be returned in otherwise stdClass is returned. Default = null
	 * @return mixed|false
	 */
	public static function getMultipleRows($query, $class=null) {
        $sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($query);
        if (isset($class)) {
            $sqlQuery->setClass($class);
        }
        $results = $sqlQuery->select()->getResults();
        if ($results->count() === 0) return false;
        return $results->toArray();
	}

	/**
     * If you want to test a private or protected method in phpUnit then call this to get the method (turned public).
     *
     * Then do this to use it:
     * $foo = TestingUtil::getClassPrivateMethod('ClassName', 'foo');
     * $obj = new ClassName();
     * $foo->invokeArgs($obj, array(...));
     *
     * @param string $className
     * @param string $methodName
     * @return ReflectionMethod
     */
	public static function getClassPrivateMethod($className, $methodName) {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
	
}