<?php

namespace GoferUtil\SDK;

/**
 * Holds the list of what options are available for a specific service. 
 * So that the service can control how to write the queries and the sql options do not need to be known to the caller
 * Subclass with getters and setters for the options needed for each service
 */
abstract class IServiceOptions {

    /**
     * All queries need a limit to be safe. Defaults to a value of 250
     * @var ServiceOption
     */
    protected $limit;

    /**
     * For pagination - all queries need a skip. Defaults to 0 meaning no skip
     * @var ServiceOption
     */
    protected $skip;

    /**
     * Defaults all properties to empty ServiceOption objects
     * Automatically checks for skip and limit set from the GET url like ?skip=0&limit=10
     * @param int $limit Optional. All queries need a limit to be safe. Defaults to a value of 250
     * @param int $skip Optional. For pagination - all queries need a skip. Defaults to 0 meaning no skip
     */
	public function __construct($limit = 250, $skip = 0) {
		$properties = get_object_vars($this);
		foreach(array_keys($properties) as $propertyName) {
			$this->$propertyName = new ServiceOption();
		}

		$this->limit->setValue($limit);
        $this->skip->setValue($skip);
	}

    /**
     * Check if the option exists with data
     * @param string $property
     * @return bool
     */
	public function exists($property) {
		if (!property_exists($this, $property)) return false;
		return isset($this->$property);
	}

    /**
     * @return ServiceOption
     */
    public function limit() {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit) {
        $this->limit->setValue($limit);
        return $this;
    }

    /**
     * @return ServiceOption
     */
    public function skip() {
        return $this->skip;
    }

    /**
     * @param int $skip
     * @return $this
     */
    public function setSkip($skip) {
        $this->skip->setValue($skip);
        return $this;
    }
	
}