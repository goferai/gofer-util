<?php

namespace GoferUtil\SQL;

use PDO;
use GoferUtil\Singleton;

/**
 * Singleton SQL Connection
 * Requires 4 global constants to be defined: MYSQL_HOST, MYSQL_DBNAME, MYSQL_USER, MYSQL_PASS
 */
class SQLConnection extends Singleton {
	
    protected $isConnected = false;
    protected $db;

    protected function __construct() {
        $this->db = new PDO("mysql:host=".MYSQL_HOST.";dbname=".MYSQL_DBNAME, MYSQL_USER, MYSQL_PASS, array(PDO::ATTR_PERSISTENT => true));
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //default is to silently set error codes. This forces them to throw PDOExceptions
        $this->db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); //force column names to be lowercase
        $this->isConnected = true;
    }
    
    /**
     * @param string $statement
     * @return number
     */
    public function exec($statement) {
    	return $this->db->exec($statement);
    }
    
    /**
     * @param string $statement
     * @return \PDOStatement
     */
    public function query($statement) {
    	return $this->db->query($statement);
    }
    
    /**
     * @param string $statement
     * @return \PDOStatement
     */
    public function prepare($statement) {
    	return $this->db->prepare($statement);
    }
    
    /**
     * Convenience function to property escape a mysql string
     * @param mixed $string
     * @return string
     */
    public function quote($string) {
    	if (!$this->isConnected) return $string;
    	return $this->db->quote($string);
    }
    
    /**
     * @return boolean
     */
    public function beginTransaction() {
    	return $this->db->beginTransaction();
    }
    
    /**
     * @return boolean
     */
    public function commit() {
    	return $this->db->commit();
    }
    
    /**
     * @return boolean
     */
    public function rollBack() {
    	return $this->db->rollBack();
    }
    
    /**
     * @return string
     */
    public function lastInsertId() {
    	return $this->db->lastInsertId();
    }

}