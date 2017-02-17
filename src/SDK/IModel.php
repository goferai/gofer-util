<?php

namespace GoferUtil\SDK;

use GoferUtil\StringUtil;

/**
 * Models are automatically generated classes that represent the data persisted in the database
 * All properties are protected. You must access data using the getters and setters.
 */
abstract class IModel implements \JsonSerializable {

	/**
	 * @var string
	 */
	CONST TABLE_NAME = '';

    /**
     * Override in model and set to return true to enable the toJSON showSensitive property.
     * This defaults to false so that property does nothing and avoids needless cloning unless you want to use it.
     * @return bool
     */
    public static function hasSensitiveData() {
        return false;
    }
	
	/**
	 * An array of all the properties. Format will be in camelCase. 
	 * @return string[]
	 */
	public static function properties() { return []; }
	
	/**
	 * An array of all the properties that are primary keys. Format will be in camelCase.
	 * @return string[]
	 */
	public static function primaryKeyProperties() { return []; }
	
	/**
	 * An array of all the properties that are NOT primary keys. Format will be in camelCase.
	 * @return string[]
	 */
	public static function nonPrimaryKeyProperties() { return []; }
	
	/**
	 * An array of all the properties that are neither primary keys or part of a unique index. Format will be in camelCase.
	 * @return string[]
	 */
	public static function nonUniqueProperties() { return []; }
	
	/**
	 * Whether the primary key is an autoIncrement column
	 * @return boolean
	 */
	public static function hasAutoIncrement() { return false; }
	
	/**
	 * {@inheritDoc}
	 * @see JsonSerializable::jsonSerialize()
	 */
	abstract public function jsonSerialize();
	
	/**
	 * @return string
	 */
	public static function tableName() {
		return static::TABLE_NAME;
	}

    /**
     * @param bool $showSensitive Optional. Set to true to show sensitive info. Defaults to false. Override in model to make this work
     * @return string
     */
	public function toJSON($showSensitive = false) {
        if (!static::hasSensitiveData() || $showSensitive) {
            return json_encode($this);
        } else {
            $cloned = clone $this;
            $cloned->hideSensitiveData();
            return json_encode($cloned);
        }
	}

    /**
     * Override in model to set any sensitive data to null. Used by toJSON
     */
    public function hideSensitiveData() { }

    /**
     * Convert the data to an html table listing the key data
     * @param string[] $whiteListedProperties Optional Pass in an array of the properties you want the table limited to. Default = array()
     * @return string
     */
	public function toHTMLTable($whiteListedProperties = array()) {
		$rows = array();
		$propertyNames = array_keys(get_object_vars($this));
		$propertyNames = (count($whiteListedProperties) >= 1) ? array_intersect($whiteListedProperties, $propertyNames) : $propertyNames ;
		foreach($propertyNames as $propertyName) {
			if (isset($this->$propertyName)) {
				if (is_string($this->$propertyName)) {
					array_push($rows, '<tr><td>'.StringUtil::convertToTitleCase($propertyName).'</td><td>'.$this->$propertyName.'</td></tr>');
				}
			}
		}
		$table = '<table>'.implode('',$rows).'</table>';
		return $table;
	}

    /**
     * @param mixed $data
     * @param string[] $blackListProperties Properties to remove. Default = array('connection', 'log')
     */
	public function initializeForData($data, $blackListProperties = ['connection', 'log']) {
        if (!isset($data)) return;
        $dataList = (!is_array($data)) ? array($data) : $data;
        foreach ($dataList as $dataItem) {
            foreach(array_keys(get_object_vars($this)) as $propertyName) {
                if (property_exists($dataItem, $propertyName) && !in_array($propertyName, $blackListProperties)) {
                    $this->$propertyName = $dataItem->$propertyName;
                }
            }
        }
    }

    /**
     * @param string $json
     * @param string[] $blackListProperties
     */
    public function initializeForJSON($json, $blackListProperties = ['connection', 'log']) {
        $this->initializeForData(json_decode($json), $blackListProperties);
    }

    /**
     * Will check if the data contains a property and if so it will initialize it as a new instance of the given class.
     * Useful for sub properties that are also classes
     * Will throw an error if the class does not exist
     * @param mixed $data
     * @param string $propertyName
     * @param string $className A fully qualified class name like '\\Gofer\\SDK\\SomeClass'. Easiest way to get this is by calling the system static method ClassName::class
     * @param boolean $isJsonString Optional Set to true if the sub property is a json string instead of a stdClass object like normal. Default = false
     */
    public function initializePropertyIfExists($data, $propertyName, $className, $isJsonString = false) {
        if (property_exists($data, $propertyName)) {
            $this->$propertyName = new $className();
            if ($isJsonString) {
                $this->$propertyName->initializeForJSON($data->$propertyName);
            } else {
                $this->$propertyName->initializeForData($data->$propertyName);
            }
        }
    }

    /**
     * Same as initializePropertyIfExists but works for a sub property that is an array of objects
     * Any previous data in the array will be removed when this is ran. It does NOT append.
     * Requires the sub object to extend from IModel for this to work
     * @param mixed $data
     * @param string $propertyName
     * @param string $className A fully qualified class name like '\\Gofer\\SDK\\SomeClass'. Use Class::classname to get it
     */
    public function initializePropertyArrayIfExists($data, $propertyName, $className) {
        if (property_exists($data, $propertyName)) {
            $this->$propertyName = array();
            foreach($data->$propertyName as $itemData) {
                /** @var IModel $newObject */
                $newObject = new $className();
                $newObject->initializeForData($itemData);
                array_push($this->$propertyName, $newObject);
            }
        }
    }

    /**
     * Escapes all strings
     * To be used before displaying data to the user
     * Override to do anything custom like allowing specific properties to contain html tags
     */
    public function escape() {
        foreach(array_keys(get_object_vars($this)) as $propertyName) {
            if (isset($this->$propertyName) && is_string($this->$propertyName)) {
                $this->$propertyName = StringUtil::escape($this->$propertyName);
            }
        }
    }

    /**
     * Sanitizes all strings
     * To be used before inserting data from the user into the database
     * Override to do anything custom like allowing specific properties to contain html tags
     */
    public function sanitize() {
        foreach(array_keys(get_object_vars($this)) as $propertyName) {
            if (isset($this->$propertyName) && is_string($this->$propertyName)) {
                $this->$propertyName = StringUtil::sanitize($this->$propertyName);
            }
        }
    }
	
}