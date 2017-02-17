<?php

namespace GoferUtil\SDK;

use GoferUtil\Log;
use GoferUtil\ObjectUtil;
use Ramsey\Uuid\Uuid;

/**
 * Responsible for creating model objects from scratch.
 * Do not use for getting objects from the database - use the service classes for that
 * Override and create setters for any data that needs to be set in order to make the model object. Then to use just call build().
 */
abstract class IModelBuilder {
	
	/**
	 * The class name of the model this factory will produce
	 * @return string
	 */
	public static function modelClass() { return ''; }

    /**
     * The class name of the collection this factory will produce
     * @return string
     */
    public static function listClass() { return ''; }
	
	/**
	 * @return IModel
	 */
	abstract public function build();

    /**
     * @param string $json
     * @return IModel
     */
	public function buildForJSON($json) {
		return $this->buildForData(json_decode($json));
	}

    /**
     * @param mixed $data
     * @return IModel
     */
	public function buildForData($data) {
		$class = static::modelClass();
        /** @var IModel $model */
		$model = new $class();
        $model->initializeForData($data);
		return $model;
	}

    /**
     * @param string $json
     * @return ICollection
     */
    public function buildListForJSON($json) {
        $class = static::listClass();
        /** @var ICollection $collection */
        $collection = new $class();
        $items = json_decode($json);
        if (!is_array($items)) return $collection;
        foreach ($items as $item) {
            $collection->add($this->buildForData($item));
        }
        return $collection;
    }

    /**
     * If you have an array of items then this will build a whole list. If it is not an array it will still make the list - just with one item
     * @param mixed $items
     * @return ICollection
     */
    public function buildListForData($items) {
        $log = new Log(basename(__FILE__));
        $log->debug('items = '.json_encode($items));
        $class = static::listClass();
        $log->debug('$class = '.$class);
        /** @var ICollection $collection */
        $collection = new $class();
        if (!is_array($items)) {
            $items = ObjectUtil::ensureArray($items);
        }
        $log->debug('2');
        foreach ($items as $item) {
            $collection->add($this->buildForData($item));
        }
        return $collection;
    }

    /**
     * Helper function to build a new UUID
     * @return string
     */
	protected function newUUID() {
	    return Uuid::uuid4()->toString();
    }
	
}