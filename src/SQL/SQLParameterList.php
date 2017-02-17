<?php

namespace GoferUtil\SQL;

use GoferUtil\SDK\ICollection;
use GoferUtil\SDK\IModel;

/**
 * A collection of SQLParemeter objects for prepared statements
 */
class SQLParameterList extends ICollection {

    /**
     * @var SQLParameter[]
     */
    protected $collection = array();

    /**
     * @return SQLParameter[]
     */
    public function toArray() {
        return parent::toArray();
    }

    /**
     * @return SQLParameter|false
     */
    public function first() {
        return parent::first();
    }

    /**
     * @inheritdoc
     * @param SQLParameter|SQLParameter[] $items
     */
    public function add($items) {
        /** @type IModel[] $items */
        parent::add($items);
    }

    /**
     * Pass the pdo statement object and the method will add the binding for each parameter in the list to the statement
     * @param \PDOStatement $statement
     */
    public function bindToStatement($statement) {
        foreach ($this->collection as $parameter) {
            $parameter->bindToStatement($statement);
        }
    }

    /**
     * Pass the prefix you want and an array of values and this will
     * @param string $prefix The prefix to add before each parameter name (including the :) example: ':prefix' will become ':prefix1', ':prefix2', etc...
     * @param array $values An array of the values to loop and create into SqlParameter objects
     * @param string $conditionString Output reference variable. Pass a string and the where clauses conditions will be populated (everything inside the ( ) brackets. Example: :prefix1, :prefix2
     * @param int $dataType Optional. The data type to pass. Defaults to PDO::PARAM_STR
     * @param bool $bindValue Optional. Whether to bind the value or the reference (reference meaning you can change the contents of the object before execute is called later). Default = true (value)
     */
    public function buildMultipleForArray($prefix, $values, &$conditionString, $dataType = \PDO::PARAM_STR, $bindValue = true) {
        $conditionStrings = [];
        for ($k = 0 ; $k < count($values); $k++) {
            $sqlParameter = new SQLParameter("{$prefix}{$k}", $values[$k], $dataType, $bindValue);
            $this->add($sqlParameter);
            $conditionStrings[] = "{$prefix}{$k}";
        }
        $conditionString = implode(',', $conditionStrings);
    }

}