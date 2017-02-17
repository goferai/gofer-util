<?php

namespace GoferUtil\SQL;

use \GoferUtil\Log;

class SQLGenerator {

    /**
     * @var Log
     */
    private $log;

    /**
     * @var string[]
     */
    private $selectParts = array();

    /**
     * @var string[]
     */
    private $selectNames = array();

    /**
     * @var string[]
     */
    private $whereParts = array();

    /**
     * @var string
     */
    private $fromTable;

    /**
     * @var string[]
     */
    private $joinParts = array();

    /**
     * @var string[]
     */
    private $groupByParts = array();

    /**
     * @var string[]
     */
    private $orderByParts = array();

    /**
     * @var int
     */
    private $skip;

    /**
     * @var int
     */
    private $_limit;
    
    public function __construct(){
        $this->log = new Log(basename(__FILE__));
    }
    
    /**
     * Call to generate the final SQL
     * Returns an empty string if there were any issues
     * @return string
     */
    public function toSQL() {
    	$sql = "";
    	if (isset($this->selectParts)) {
    		$sql =
                $this->buildSelect().
                $this->buildFrom().
                $this->buildJoins().
                $this->buildWhere().
                $this->buildGroupBy().
                $this->buildOrderBy().
                $this->buildLimit().
                $this->buildSkip();
    	}
    	return $sql;
    }
    
    /**
     * Call to add items to the array. Every call will add more to the list.
     * @param mixed $selectParts String or Array of columns names
     * @param mixed $selectNames Optional Pass a list of names for each column. Required if you are using urlOptions that may need to change the column list around. Default = NULL
     * @return $this
     */
    public function select($selectParts, $selectNames = null) {
    	$this->selectParts = $this->addToArray($this->selectParts, $this->ensureArray($selectParts));
    	$this->selectNames = $this->addToArray($this->selectNames, $this->ensureArray($selectNames));
    	return $this;
    }

    /**
     * Deletes any select columns already in there so you can add fresh ones
     * @return $this
     */
    public function emptySelect() {
        $this->selectParts = [];
        $this->selectNames = [];
        return $this;
    }
    
    /**
     * @param string $from
     * @return $this
     */
    public function from($from) {
    	$this->fromTable = $from;
    	return $this;
    }
    
    /**
     * Add to the where clause. Every call will add more to the where clause conditions
     * @param mixed $where String or array of string conditions
     * @return $this
     */
    public function where($where) {
    	$this->whereParts = $this->addToArray($this->whereParts, $this->ensureArray($where));
    	return $this;
    }
    
    /**
     * Add a join
     * @param string $joinType Either left outer, right outer, or inner
     * @param string $join The actual join string - include the ON clause
     * @return $this
     */
    public function join($joinType, $join) {
    	$this->joinParts = $this->addToArray($this->joinParts, $this->ensureArray($joinType." join ".$join));
    	return $this;
    }
    
    /**
     * Add a group by column
     * @param mixed $groupBy A string or array of strings to be added to the group by list
     * @return $this
     */
    public function groupBy($groupBy) {
    	$this->groupByParts = $this->addToArray($this->groupByParts, $this->ensureArray($groupBy));
    	return $this;
    }
    
    /**
     * Add an order by column
     * @param mixed $orderBy A string or array of strings to be added to the order by list
     * @return $this
     */
    public function orderBy($orderBy) {
    	$this->orderByParts = $this->addToArray($this->orderByParts, $this->ensureArray($orderBy));
    	return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * @param int $skip
     * @return $this
     */
    public function skip($skip) {
        $this->skip = $skip;
        return $this;
    }

    /**
     * @return string
     */
    protected function buildSelect() {
    	$combinedColumnsAndNames = $this->getCombinedColumnsAndNames();
    	//$columnsIncluded = $this->includeColumnsRequested($combinedColumnsAndNames);
        $count = 1;
        $columnsSQL = "";
        foreach ($combinedColumnsAndNames as $key=>$value) {
            $comma = ($count >= 2) ? ", " : "";
            $columnsSQL .= "$comma$key".((isset($value)) ? " as ".$value : "" );
            $count = $count + 1;
        }
        return "select ".$columnsSQL." ";
    }
    
    protected function buildFrom() {
    	return "from ".$this->fromTable." ";
    }
    
    protected function buildJoins() {
    	return implode(" ", $this->joinParts)." ";
    }
    
    protected function buildWhere() {
    	$sql = (count($this->whereParts) >= 1) ? "where " : "" ;
    	$sql .= implode(" and " , $this->whereParts);
    	return $sql." ";
    }
    
    protected function buildGroupBy() {
    	$sql = (count($this->groupByParts) >= 1) ? "group by " : "" ;
    	$sql .= implode(", " , $this->groupByParts);
    	return $sql." ";
    }
    
    protected function buildOrderBy() {
        $sql = (count($this->orderByParts) >= 1) ? "order by " : "" ;
        $sql .= implode(", " , $this->orderByParts);
        return $sql." ";
    }
    
    protected function buildLimit() {
        if (isset($this->_limit)) {
            return 'limit '.$this->_limit.' ';
        } else {
    		return '';
    	}
    }

    protected function buildSkip() {
        if (isset($this->skip)) {
            return 'offset '.$this->skip.' ';
        } else {
            return '';
        }
    }
    
    private function getCombinedColumnsAndNames() {
    	if (isset($this->selectNames) && count($this->selectNames)  == count($this->selectParts)) {
    		$columns = array_combine($this->selectParts, $this->selectNames);
    	} else {
    		$columns = array_combine($this->selectParts, array_fill(0, count($this->selectParts), null));
    	}
    	return $columns;
    }

    
    private function ensureArray($input) {
    	return (is_array($input)) ? $input : array($input);
    }

    /**
     * @param array $destinationArray
     * @param array $additionalArray
     * @return array
     */
    private function addToArray($destinationArray, $additionalArray) {
    	return array_merge($destinationArray, $additionalArray);
    }
    
}