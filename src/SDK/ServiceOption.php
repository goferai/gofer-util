<?php

namespace GoferUtil\SDK;

/**
 * The object returned from IServiceOptions when using a getter.
 */
class ServiceOption {
		
	/**
	 * @var mixed
	 */
	protected $optionValue;
	
	/**
	 * @param mixed $optionValue
	 */
	public function __construct($optionValue = null) {
		$this->optionValue = $optionValue;
	}
	
	/**
	 * Check if data exists and it is not null
	 * @return boolean
	 */
	public function exists() {
		return isset($this->optionValue);
	}
	
	/**
	 * Check if data is missing or null - opposite of exists
	 * @return boolean
	 */
	public function notExists() {
		return !isset($this->optionValue);
	}
	
	/**
	 * @return mixed
	 */
	public function value() {
		return $this->optionValue;
	}
	
	/**
	 * @param mixed $optionValue
	 * @return $this
	 */
	public function setValue($optionValue = null) {
		$this->optionValue = $optionValue;
		return $this;
	}
	
}