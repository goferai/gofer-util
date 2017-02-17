<?php

namespace GoferUtil;

class ObjectUtil {

    /**
     * Returns true if the object is null, false, not an object, or has no properties
     * If it is a custom object (not stdclass) then it does not check for properties and instead checks for a method calls isEmpty() defined by that class itself.
     * @param mixed $object
     * @return bool
     */
    public static function isEmpty($object) {
        if (empty($object) || !is_object($object)) {
            return true;
        }
        if (is_a($object, '\stdClass')) {
            return empty(get_object_vars($object));

        }
        if (method_exists($object, 'isEmpty')) {
            $emptyMethod = 'isEmpty';
            return $object->$emptyMethod();
        }
        return false;
    }

    public static function tryGetObject($object, $valueIfNull = "") {
        return (!isset($object)) ? $valueIfNull : $object ;
    }
    
    /**
     * Takes an object or an array of objects and converts the single object into an array of objects if it isn't already.
     * @param mixed $object
     * @return array
     */
    public static function ensureArray($object) {
    	$objects = (!is_array($object)) ? array($object) : $object;
    	return $objects;
    }

    /**
     * Returns the value for that object's property if it is set. Otherwise returns the valueIfNull value.
     * @param \stdClass $object
     * @param string $propertyName
     * @param string $valueIfNull Optional. Default null
     * @return null|string
     */
    public static function tryGetObjectProperty($object, $propertyName, $valueIfNull = null) {
    	if (!isset($object)) return $valueIfNull;
    	return (!property_exists($object, $propertyName)) ? $valueIfNull : $object->$propertyName ;
    }
    
    /**
     * Returns a cloned copy of an object with all properties removed except those requested
     * @param mixed $object
     * @param array $whiteListProperties
     * @return \stdClass
     */
    public static function getClonedObjectWithProperties($object, $whiteListProperties) {
    	$clonedObject = clone $object;
    	$properties = get_object_vars($clonedObject);
    	foreach(array_keys($properties) as $propertyName) {
    		if (!in_array($propertyName, $whiteListProperties, true)) {
    			unset($clonedObject->{$propertyName});
    		}
    	}
    	return $clonedObject;
    }
    
    /**
     * Return a clone of the object with all properties that are null unset.
     * NOTE: Will not dig deep into any nested objects the way it is written.
     * @param mixed $object
     * @return mixed
     */
    public static function getClonedObjectWithoutNullProperties($object) {
    	$clonedObject = clone $object;
    	$properties = get_object_vars($clonedObject);
    	foreach ($properties as $propertyName => $propertyValue) {
    		if (is_null($propertyValue)) {
    			unset($clonedObject->{$propertyName});
    		}
    	}
    	return $clonedObject;
    }
    
    /**
     * Merges the properties from object2 into object1. Resulting values will always become an array of values in object 1
     * @param mixed $object1
     * @param mixed $object2
     * @param string $property The property name to search for
     * @return array The merged values or the original values if nothing is found
     */
    public static function mergePropertiesAsArray($object1, $object2, $property) {
    	if (!property_exists($object1, $property)) {
    		$object1->$property = array();
    	}
    	if (property_exists($object2, $property)) {
    		return array_merge(ObjectUtil::ensureArray($object1->$property), ObjectUtil::ensureArray($object2->$property));
    	}
    	return $object1->$property;
    }

    /**
     * Pass in an object and it will return the class name without the namespace
     * @param mixed $object Can be an object instance or it can be a class name derived from calling the static funciton SomeClass::class
     * @return string
     */
    public static function getObjectsShortClassName($object) {
        return (new \ReflectionClass($object))->getShortName();
    }

    /**
     * Pass in an object and it will return the namespace
     * @param mixed $object Can be an object instance or it can be a class name derived from calling the static funciton SomeClass::class
     * @return string
     */
    public static function getObjectsNamespace($object) {
        return (new \ReflectionClass($object))->getNamespaceName();
    }

    /**
     * Pass an array of objects and it'll add this one value as a new property to each one
     * @param array $objects
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return mixed
     */
    public static function setPropertyValueForObjects($objects, $propertyName, $propertyValue) {
    	foreach ($objects as $object) {
    		$object->$propertyName = $propertyValue;
    	}
    	return $objects;
    }
    
    /**
     * Populate an object with data from another stdClass object
     * Call initializePropertyIfExists if you need to initialize sub properties too
     * ONLY WORKS FOR PUBLIC PROPERTIES
     * @param mixed $object The object to apply the data to
     * @param mixed $data The stdClass data we want to move to the object. Can also be an array of stdClass objects (if different array items might not contain all properties)
     * @param string[] $blackListProperties List of properties to exclude
     */
    public static function initializeForData($object, $data, $blackListProperties = array('connection', 'log')) {
        $log = new Log(basename(__FILE__));
        $log->debug('data = '.json_encode($data));
        $log->debug('get_object_vars($object) = '.json_encode(get_object_vars($object)));
    	if (!isset($data)) return;
    	$dataList = (!is_array($data)) ? array($data) : $data;
    	foreach ($dataList as $dataItem) {
    		foreach(array_keys(get_object_vars($object)) as $propertyName) {
    			if (property_exists($dataItem, $propertyName) && !in_array($propertyName, $blackListProperties)) {
                    $object->$propertyName = $dataItem->$propertyName;
                }
    		}
    	}
    }
    
    /**
     * Pass in a json string and this will initialize the current instances properties.
     * Call initializePropertyIfExists if you need to initialize sub properties as objects too
     * ONLY WORKS FOR PUBLIC PROPERTIES
     * @param mixed $object The object to apply the data to
     * @param string $json The json data
     * @param string[] $blackListProperties List of properties to exclude
     */
    public static function initializeForJSON($object, $json, $blackListProperties = array('connection', 'log')) {
    	ObjectUtil::initializeForData($object, json_decode($json), $blackListProperties);
    }
    
    /**
     * Will check if the data contains a property and if so it will initialize it as a new instance of the given class.
     * Useful for sub properties that are also classes
     * !!!! Class constructor must NOT require an input for this to work !!!!
     * ONLY WORKS FOR PUBLIC PROPERTIES
     * Will throw an error if the class does not exist
     * @param mixed $object The object to apply the data to
     * @param mixed $data
     * @param string $propertyName
     * @param string $className A fully qualified class name like '\\Gofer\\SDK\\SomeClass'. Easiest way to get this is by calling the system static method ClassName::class
     * @param boolean $isJsonString Optional Set to true if the sub property is a json string instead of a stdClass object like normal. Default = false
     */
    public static function initializePropertyIfExists($object, $data, $propertyName, $className, $isJsonString = false) {
    	if (property_exists($data, $propertyName)) {
    		$object->$propertyName = new $className();
    		if ($isJsonString) {
    			$object->$propertyName->initializeForJSON($data->$propertyName);
    		} else {
    			$object->$propertyName->initializeForData($data->$propertyName);
    		}
    	}
    }
    
    /**
     * Same as initializePropertyIfExists but works for a sub property that is an array of objects
     * Any previous data in the array will be removed when this is ran. It does NOT append.
     * ONLY WORKS FOR PUBLIC PROPERTIES
     * @param mixed $object The object to apply the data to
     * @param mixed $data
     * @param string $propertyName
     * @param string $className A fully qualified class name like '\\Gofer\\SDK\\SomeClass'
     */
    public static function initializePropertyArrayIfExists($object, $data, $propertyName, $className) {
    	if (property_exists($data, $propertyName)) {
    		$object->$propertyName = array();
    		foreach($data->$propertyName as $itemData) {
    			$newObject = new $className();
                ObjectUtil::initializeForData($newObject, $itemData);
    			array_push($object->$propertyName, $object);
    		}
    	}
    }

    /**
     * Returns true if the class exists
     * @param string $entityClass A fully qualified class name like '\\Gofer\\SDK\\SomeClass'
     * @return bool
     */
    public static function doesClassExist($entityClass) {
        return class_exists($entityClass);
    }

}