<?php

namespace GoferUtil\SQL;

use PDO;
use PDOException;
use \GoferUtil\Log;

class SQLQuery {
	
	/**
	 * @var \GoferUtil\Log
	 */
    protected $log;
    
    /**
     * @var SQLConnection
     */
    protected $connection;
    
    /**
     * @var string
     */
    protected $query;
    
    /**
     * @var string
     */
    protected $class;
    
    /**
     * @var SQLResults
     */
    protected $sqlResults;
    
    /**
     * @param SQLConnection $connection
     */
    public function __construct(SQLConnection $connection) {
        $this->log = new Log(basename(__FILE__));
        $this->connection = $connection;
    }

    /**
     * Alias for calling the PDO: quote under the covers to escape bad strings coming from the user
     * @param $string
     * @return string
     * @internal param mixed $sting
     */
    public function quote($string) {
    	return $this->connection->quote($string);
    }
    
    /**
     * @param string $query
     * @return $this
     */
    public function setQuery($query) {
    	$this->query = $query;
    	return $this;
    }
    
    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class) {
    	$this->class = $class;
    	return $this;
    }
    
    /**
     * @return SQLResults
     */
    public function getResults() {
    	return $this->sqlResults;
    }
    
    /**
     * Run some sql statement and after call getResults() to return a sqlResults object. If there's any errors an empty sqlResults object is returned.
     * @return $this
     */
    public function select() {
    	$this->sqlResults = new SQLResults();
    	if (!isset($this->connection)) throw new \RuntimeException('Connection not set');
        try {
            $this->log->debug('query = '.$this->query);
            $sql = $this->connection->query($this->query);
            if (!$sql) throw new PDOException("error");
            if (isset($this->class)) {
            	$results = $sql->fetchAll(PDO::FETCH_CLASS, $this->class);
            } else {
            	$results = $sql->fetchAll(PDO::FETCH_OBJ); //returns array of std objects or false on error
            }
            if ($results === false) throw new PDOException("error");
            $this->sqlResults->set($results);
            return $this;
        } catch(PDOException $e) {
            $this->log->error("select - Error = ", $e);
            return $this;
        }
    }

    /**
     * Run some sql statement as a prepared statement
     * After call getResults() to return a sqlResults object. If there's any errors an empty sqlResults object is returned.
     * @param SQLParameterList $sqlParameterList
     * @return $this
     */
    public function selectSecure($sqlParameterList) {
        $this->sqlResults = new SQLResults();
        if (!isset($this->connection)) throw new \RuntimeException('Connection not set');
        try {
            $this->log->debug('query = '.$this->query);
            $stmt = $this->connection->prepare($this->query);
            $sqlParameterList->bindToStatement($stmt);
            $executeResult = $stmt->execute();
            if (!$executeResult) throw new PDOException("error");
            if (isset($this->class)) {
                $results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->class);
            } else {
                $results = $stmt->fetchAll(PDO::FETCH_OBJ); //returns array of std objects or false on error
            }
            if ($results === false) throw new PDOException("error");
            $this->sqlResults->set($results);
            return $this;
        } catch(PDOException $e) {
            $this->log->error("select - Error = ", $e);
            return $this;
        }
    }

 	/**
 	 * Pass in a bulk upsert sql statement (derived using the BulkSQLUpsertGenerator) and this will run them all and return the row count
 	 * @param string $sql
 	 * @return int|false Number of rows affected or false if there was a problem
 	 */
 	public function bulkUpsert($sql) {
 		try {
 			$rowCount = $this->connection->exec($sql);
 			$this->log->debug('bulkUpsert exec result - rowCount = '.$rowCount);
 			return $rowCount;
 		} catch(PDOException $e) {
 			$this->log->error("bulkUpsert - Error = ", $e);
 			return false;
 		}
 	}

}