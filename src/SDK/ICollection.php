<?php

namespace GoferUtil\SDK;

use GoferUtil\ObjectUtil;

/**
 * A Collection of model objects
 */
class ICollection implements \IteratorAggregate, \Countable, \JsonSerializable  {
	
	/**
	 * The array of data elements
	 * @var array
	 */
	protected $collection = array();

    /**
     * Override in model and set to return true to enable the toJSON showSensitive property.
     * This defaults to false so that property does nothing and avoids needless cloning unless you want to use it.
     * @return bool
     */
    public static function hasSensitiveData() {
        return false;
    }

    /**
	 * Allows you to perform a foreach over the collection automatically
	 * {@inheritDoc}
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->collection);
	}
	 
	/**
	 * {@inheritDoc}
	 * @see Countable::count()
	 */
	public function count($mode = null) {
		return count($this->collection);
	}

    /**
     * Alias function to check if the count is 0
     * Same as $this->count() === 0
     * @return bool
     */
	public function isEmpty() {
	    return $this->count() === 0;
    }

    /**
     * Add items. Can be one item or an array of many items
     * @param IModel|IModel[] $items
     * @internal param IModel|IModel[] $item
     */
	public function add($items) {
	    $items = ObjectUtil::ensureArray($items);
		$this->collection = array_merge($this->collection, $items);
	}

    /**
     * Remove an item at the specified index. 0 based.
     * @param int $index
     */
    public function remove($index) {
        array_splice($this->collection, $index, 1);
    }
	
	/**
	 * Set all items replacing the entire collection
	 * @param IModel[] $items
	 */
	public function replace($items) {
		$this->collection = $items;
	}

    /**
     * @param bool $showSensitive Optional. Set to true to show sensitive info. Default = false
     * @return string
     */
    public function toJSON($showSensitive = false) {
        if (!static::hasSensitiveData() ||$showSensitive) {
            return json_encode($this->collection);
        } else {
            $clonedCollection = [];
            foreach($this->collection as $item) {
                $cloned = clone $item;
                $cloned->hideSensitiveData();
                array_push($clonedCollection, $cloned);
            }
            return json_encode($clonedCollection);
        }
    }

    public function toArray() {
        return $this->collection;
    }

    /**
     * Return the first item if available. Returns false if no first exists. Call count() first to make sure it exists
     * @return IModel|false
     */
    public function first() {
        if (count($this->collection) === 0) return false;
        return $this->collection[0];
    }

    /**
     * Return a random item from the list. Returns false if no items exist. Call count() first to make sure it exists
     * @return IModel|false
     */
    public function random() {
        $count = count($this->collection);
        if ($count === 0) return false;
        return $this->collection[rand(0, $count-1)];
    }

    /**
     * Return the item at the index position if available. Returns false if it doesn't exist. Call count() first to make sure it exists
     * !!! Index is ZERO based
     * @param int $index Required. Zero based index of item position
     * @return IModel|false
     */
    public function getItem($index) {
        if (count($this->collection) < ($index + 1)) return false;
        return $this->collection[$index];
    }

    /**
     * For use when filtering to keep the original items.
     * @var ICollection[]
     */
    protected $originalCollection;

    /**
     * After filtering you can call this to get back the original items.
     * @return $this
     */
    public function resetFilter() {
        if (!isset($this->originalCollection)) return $this;
        $this->collection = $this->originalCollection;
        $this->originalCollection = null;
        return $this;
    }

    /**
     * Like underscore.js's pluck method. This will return an array with just that single property plucked from each item in the collection.
     * Returns an empty array if nothing is found.
     * @param $propertyName
     * @return array
     */
    public function pluck($propertyName) {
        $pluckedItems = [];
        $getterName = 'get'.ucfirst($propertyName);
        foreach ($this->collection as $item) {
            array_push($pluckedItems, $item->$getterName());
        }
        return $pluckedItems;
    }

    public function jsonSerialize() {
        return $this->collection;
    }

    /**
     * Calls hideSensitiveData for every model in the collection
     */
    public function hideSensitiveData() {
        foreach ($this->collection as $item) {
            /** @type IModel $item */
            $item->hideSensitiveData();
        }
    }

    /**
     * Escapes all items in the collection
     * To be used before displaying data to the user
     */
    public function escape() {
        foreach($this->collection as $item) {
            $item->escape();
        }
    }

    /**
     * Sanitizes all items in the collection
     * To be used before inserting data from the user into the database
     */
    public function sanitize() {
        foreach($this->collection as $item) {
            $item->sanitize();
        }
    }

}