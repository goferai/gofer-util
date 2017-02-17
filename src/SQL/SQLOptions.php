<?php

namespace GoferUtil\SQL;

/**
 * @property string[] $columns An array of columns to be added to the sql query
 * @property string[] $orderColumns An array of order by columns to be added to the sql query
 * @property string[] $where An array of where clauses to be added to the sql query
 * @property int $limit An integer limiting the rows
 */
class SQLOptions {
	
	public $columns;
	public $orderColumns;
	public $where;
	public $limit;
	
	public function initializeForURLParameters() {
		
		if (isset($_GET ['columns'])) {
			$this->columns = explode(",", $_GET ['columns']);
		}
		
		if (isset($_GET ['orderColumns'])) {
			$this->orderColumns = explode(",", $_GET ['orderColumns']);
		}
		
		if (isset($_GET ['where'])) {
			$this->where = explode(",", $_GET ['where']);
		}
		
		if (isset($_GET ['limit'])) {
			$this->limit = intval($_GET ['limit']);
		}
	}
}