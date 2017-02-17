<?php

namespace GoferUtil\SQL;

use \GoferUtil\Log;
use GoferUtil\StringUtil;
use Underscore\Types\Arrays;


/**
 * Builds a sql insert statement with multiple values for each row and inserts or updates them
 * Requires that every row of the data have every property
 * Fields to insert will be taken straight from the properties in the data unless you exclude any.
 * NOTE: They will be converted to underscore case so MasterId will become master_id
 * Required data to set: $table, $data, $primaryKeys
 * Optional data to set: $propertiesToExclude,  $extraDataToPrepend Optional.
 * Call toSQL to get the final result
 */
class BulkSQLUpsertGenerator {

    /**
     * @var Log
     */
    private $log;

    /**
     * Array of table columns that represent the primary keys. Needed for the on duplicate key update part.
     * @var string[]
     */
    private $primaryKeys = array();

    /**
     * Optional. Specify a list of property names to remove from the data. The format should match the data (not the table's column name format)
     * @var string[]
     */
    private $propertiesToExclude = array();

    /**
     * Optional. Specify a column name and value to add to every row.
     * @var array
     */
    private $extraDataToPrepend = array();

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $table;
    
    public function __construct(){
        $this->log = new Log(basename(__FILE__));
    }

    /**
     * Required. Array of table columns that represent the primary keys. Needed for the on duplicate key update part.
     * @param \string[] $primaryKeys
     * @return $this
     */
    public function primaryKeys($primaryKeys) {
        $this->primaryKeys = $primaryKeys;
        return $this;
    }

    /**
     * Optional. Specify a list of property names to remove from the data. The format should match the data (not the table's column name format)
     * @param \string[] $propertiesToExclude
     * @return $this
     */
    public function propertiesToExclude($propertiesToExclude) {
        $this->propertiesToExclude = $propertiesToExclude;
        return $this;
    }

    /**
     * Optional - Specify a column name and value to add to every row.
     * @param array $extraDataToPrepend
     * @return $this
     */
    public function extraDataToPrepend($extraDataToPrepend) {
        $this->extraDataToPrepend = $extraDataToPrepend;
        return $this;
    }

    /**
     * Required - add the data to be included in the bulk upsert values
     * @param array $data
     * @return $this
     */
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Required. Set the mysql table name
     * @param string $table
     * @return $this
     */
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Call to generate the final SQL
     * Returns an empty string if there were any issues
     * @return string
     */
    public function toSQL() {
    	$sql = "";
    	if (count($this->primaryKeys) === 0 || !isset($this->table) || !isset($this->data)) return $sql;

        $columns = array();
        $updates = array();
        $values = array();

        if (count($this->extraDataToPrepend) >= 1) {
            foreach (array_keys($this->extraDataToPrepend) as $columnName) {
                array_push($columns, $columnName);
            }
        }

        $fields = array_keys(get_object_vars($this->data[0]));
        $fields = Arrays::without($fields, $this->propertiesToExclude);
        foreach($fields as $field) {
            $column = StringUtil::convertTitleToUnderscoreCase($field);
            array_push($columns, $column);
            if (Arrays::contains($this->primaryKeys, $column) === false) {
                array_push($updates, "$column = VALUES($column)");
            }
        }

        foreach($this->data as $value) {
            $updatedFieldValues = array();

            //exclude properties
            foreach($this->propertiesToExclude as $propertyToExclude) {
                if(property_exists($value, $propertyToExclude)) {
                    unset($value->$propertyToExclude);
                }
            }
            $fieldValues = array_values(get_object_vars($value));

            //prepend any values
            if (count($this->extraDataToPrepend) >= 1) {
                foreach (array_values($this->extraDataToPrepend) as $columnValue) {
                    array_push($updatedFieldValues, $columnValue);
                }
            }

            //Build the values array
            foreach($fieldValues as $fieldValue) {
                $fieldValue = str_replace("'", "''", $fieldValue); //escape
                array_push($updatedFieldValues, $fieldValue);
            }
            array_push($values, "('".implode("', '", $updatedFieldValues)."')");
        }
        $sql = "insert into $this->table
 				(".implode(', ', $columns).")
 				values ".implode(", ", $values). "
 				on duplicate key update ".implode(", ", $updates);
        $this->log->debug("buildBulkUpsertSQL = $sql");
        return $sql;
    }
    

}