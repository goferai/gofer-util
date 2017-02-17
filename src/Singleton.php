<?php

namespace GoferUtil;

/**
 * A parent class for subclasses to extend to be singletons
 */
abstract class Singleton {
	private static $instances = array();
	
	/**
	 * Subclasses should override this with whatever they need to do during construction
	 */
	protected function __construct() {}
	protected function __clone() {}
	public function __wakeup(){
		throw new \Exception("Cannot unserialize singleton");
	}
	
	/**
	 * Methods should call this static function to get an instance of a singleton object for use. Does not need to be overridden by subclass
	 * @return $this
	 */
	public static function getInstance() {
		$cls = get_called_class(); // late-static-bound class name
		if (!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static;
		}
		return self::$instances[$cls];
	}
}