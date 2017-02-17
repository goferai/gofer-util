<?php

namespace GoferUtil\SQL;

use GoferUtil\Log;

class SQLResults implements \IteratorAggregate, \Countable {

    /**
     * @var Log
     */
    protected $log;

	/**
	 * @var mixed
	 */
	protected $items = array();

    public function __construct() {
        $this->log = new Log(basename(__FILE__));
    }

    /**
	 * {@inheritdoc}
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
        $this->log->debug('getIterator items = '.json_encode($this->items));
		return new \ArrayIterator($this->items);
	}
	
	/**
	 * @param array $items
	 */
	public function set($items) {
		$this->items = $items;
        $this->log->debug('set items = '.json_encode($this->items));
	}
	
	/**
	 * True if there are any results
	 * @return boolean
	 */
	public function hasResults() {
		return $this->count() >= 1;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Countable::count()
	 */
	public function count($mode = null) {
		return count($this->items);
	}
	
	/**
	 * Returns the first item
	 * @return mixed
	 */
	public function first() {
		return $this->items[0];
	}

    /**
     * Gets the item at the specified index
     * @param int $index
     * @return mixed
     */
    public function get($index) {
        return $this->items[$index];
    }
	
	/**
	 * Returns all items as an array
	 * @return array
	 */
	public function all() {
		return $this->items;
	}

    /**
     * @return mixed
     */
    public function toArray() {
        return $this->items;
    }
	
}