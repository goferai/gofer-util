<?php

namespace GoferUtil;

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\Client;

class Neo4jConnectionManager {
	
    public $isConnected = false;
    
    /**
     * @var Client
     */
    public $db;
    
    protected $log;

    /**
     * Neo4jConnectionManager constructor.
     * @param string $connectionString format = NEO4J_HTTP_TYPE.'://'.NEO4J_USERNAME.':'.NEO4J_PASSWORD.'@'.NEO4J_HOST.':'.NEO4J_PORT
     */
    public function __construct($connectionString) {
    	$this->log = new Log(basename(__FILE__).'|'.ObjectUtil::getObjectsShortClassName($this));
        try {
			$this->log->debug($connectionString);
			$this->db = ClientBuilder::create()
										->addConnection('default', $connectionString)
					    				->build();
            $this->isConnected = true;
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Takes a query and populates all the params. For use with logging to see what the query was in full.
     * @param string $query
     * @param array $params
     * @return mixed|string
     */
    public static function populateParamsInQueryString($query, $params) {
    	$parameters = (isset($params['params'])) ? $params['params'] : $params ;
    	$prefix = (isset($params['params'])) ? '{params}.' : '{' ;
    	$suffix = (isset($params['params'])) ? '' : '}' ;
    	foreach ($parameters as $key=>$value) {
    		$replacement = $value;
    		if (is_bool($value)) {
    			$replacement = ($value) ? 'true' : 'false';
    		}
    		if (is_string($value)) {
    			$replacement = "'$value'";
    		}
    		$lookup = $prefix.$key.$suffix;
    		$query = str_replace($lookup, $replacement, $query);
    	}
    	return $query;
    }
    
}