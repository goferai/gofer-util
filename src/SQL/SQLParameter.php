<?php

namespace GoferUtil\SQL;

use PDO;

/**
 * An object for passing to SQLQuery when using a bindValue or bindParam call with a prepared statement.
 */
class SQLParameter {
	
	/**
	 * @var string
	 */
    protected $name;
    
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * @var int
     */
    protected $dataType;

    /**
     * @var boolean
     */
    protected $bindValue;

    /**
     * SQLParameter constructor.
     * @param string $name The parameter's name (including the prefixed colon)
     * @param mixed $value The value to pass
     * @param int $dataType Optional. The data type to pass. Defaults to PDO::PARAM_STR
     * @param bool $bindValue Optional. Whether to bind the value or the reference (reference meaning you can change the contents of the object before execute is called later). Default = true (value)
     */
    public function __construct($name, $value, $dataType = PDO::PARAM_STR, $bindValue = true) {
        $this->name = $name;
        $this->value = $value;
        $this->dataType = $dataType;
        $this->bindValue = $bindValue;
    }

    /**
     * Pass the pdo statement object and the method will add the binding to the statement
     * @param \PDOStatement $statement
     */
    public function bindToStatement($statement) {
        if ($this->bindValue) {
            $statement->bindValue($this->name, $this->value, $this->dataType);
        } else {
            $statement->bindParam($this->name, $this->value, $this->dataType);
        }
    }

}