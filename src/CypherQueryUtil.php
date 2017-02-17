<?php

namespace GoferUtil;

/**
 * This class exists to make some common calls very simple. Use the native neoclient api to do anything beyond this
 *
 * Instructions:
 * see https://github.com/graphaware/neo4j-php-client
 */
class CypherQueryUtil {

    private $log;

    /**
     * @var Neo4jConnectionManager
     */
    private $connection;

    public function __construct(Neo4jConnectionManager $connection) {
        $this->log = new Log(basename(__FILE__));
        $this->connection = $connection;
    }

    /**
     * Alias function to return an array of a single thing back from a cypher query's return statement.
     * Only supports one thing - the first thing in the return statement
     * Return an array of objects back, or an empty array if nothing is found or false on error
     * @param string $query
     * @param string $class Optional Pass to make the return objects be instances of this class
     * @return mixed[]|false
     */
    public function select($query, $class = null) {
    	if (!isset($this->connection)) throw new \RuntimeException('Connection not set');
    	if (!$this->connection->isConnected) return false;
    	try {
    		$this->log->debug("select - query = ".$query." - class = ".$class);
    		$result = $this->connection->db->run($query);
    		$records = $result->getRecords();
    		$classObjects = array();
    		foreach ($records as $record) {
    			$recordKeys = $record->keys();
    			$node = $record->get($recordKeys[0]);
    			$newClass = $this->convertNodeToObject($node, $class);
    			array_push($classObjects, $newClass);
    		}
    		return $classObjects;
    	} catch (\Exception $e) {
    		$this->log->error("select - Error = ", $e);
    		return false;
    	}
    }

    /**
     * @param Node $node
     * @param string $class
     * @return \stdClass
     */
    public function convertNodeToObject($node, $class = null) {
    	if (isset($class)) {
    		$newClass = new $class();
    	} else {
    		$newClass = new \stdClass();
    	}
    	$nodeKeys = $node->keys();
    	$object = new \stdClass();
    	foreach ($nodeKeys as $key) {
    		$object->$key = $node->value($key);
    	}
    	$newClass->initializeForData($object);
    	return $newClass;
    }

    /**
     * Alias function to run any cypher query. Call the cypher client methods yourself afterward
     * @param string $query
     * @param mixed $params
     * @return \GraphAware\Common\Result\Result
     */
    public function execute($query, $params = null) {
    	return $this->connection->db->run($query, $params);
    }

}