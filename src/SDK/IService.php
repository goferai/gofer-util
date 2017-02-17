<?php

namespace GoferUtil\SDK;

use GoferUtil\Log;
use GoferUtil\SQL\SQLParameterList;
use GoferUtil\StringUtil;
use GoferUtil\SQL\SQLGenerator;
use GoferUtil\ObjectUtil;
use GoferUtil\SQL\SQLQuery;
use GoferUtil\SQL\SQLConnection;
use Underscore\Types\Arrays;

/**
 * Holds the logic about manipulating persisted models to/from the database. Plus add any additional actions on these models.
 * Contains basic queries for get, insert, etc... Override the protected buildSQL methods if you need more advanced logic.
 */
abstract class IService {

    /**
     * @var Log
     */
    protected $log;

	public function __construct() {
        $this->log = new Log(basename(__FILE__).'|'.ObjectUtil::getObjectsShortClassName($this));
	}

    /**
     * @return IModel
     */
    protected function getModelClass() {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return 'GoferUtil\SDK\Models\AppModel';
    }

    /**
     * @return string
     */
    abstract protected function tableAlias();

    /**
     * @param $string
     * @return string
     */
	protected function quote($string) {
	    return SQLConnection::getInstance()->quote($string);
    }

    /**
     * Adds quotes to each item in an array. For use when needing to add an IN clause to a where filter.
     * To use call it like this:
     * "(".implode(', ', $this->quoteArray($serviceOptions->lookupLatestEmailWithAttendeePositions()->value())).')'
     * @param string[] $array
     * @return string[]
     */
    protected function quoteArray($array) {
        $cleanedArray = [];
        foreach($array as $item) {
            array_push($cleanedArray, $this->quote($item));
        }
        return $cleanedArray;
    }
	
	/**
	 * Return a single model or false if there were no results
     * If buildGetSQL defines any sqlParemeters then a prepared statement is used. Otherwise a basic query is used.
	 * @param IServiceOptions $serviceOptions
	 * @return IModel|false
	 */
	public function get($serviceOptions) {
	    $this->log->debug('get - start');
	    $sqlParameterList = new SQLParameterList();
        $sqlGenerator = $this->buildGetSQL($serviceOptions, $sqlParameterList);
        if ($serviceOptions->limit()->exists()) {
            $sqlGenerator->limit($serviceOptions->limit()->value());
        }
        if ($serviceOptions->skip()->exists() && $serviceOptions->skip()->value() > 0) {
            $sqlGenerator->skip($serviceOptions->limit()->value());
        }
		$sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sqlGenerator->toSQL())
                 ->setClass($this->getModelClass());
        if ($sqlParameterList->isEmpty()) {
            $sqlQuery->select();
        } else {
            $sqlQuery->selectSecure($sqlParameterList);
        }
        $results = $sqlQuery->getResults();
		if (!$results->hasResults()) return false;
		return $results->first();
	}

    /**
     * Override to add additional where statements.
     * Call parent::buildGetSQL to reuse the existing code and add your own afterward.
     * Override whole thing if existing code will not work.
     * No need to add limit or skip - just set them in the serviceOptions and they'll get applied automatically before the get or getList call runs
     * @param IServiceOptions $serviceOptions
     * @param SQLParameterList $sqlParameterList Optional. Pass an array by reference. This will be filled out with the sql parameters to use later in a secure select prepared statement. Default = null
     * @return SQLGenerator
     */
    protected function buildGetSQL(/** @noinspection PhpUnusedParameterInspection */ $serviceOptions, &$sqlParameterList = null) {
        $tableAlias = $this->tableAlias();
        $className = $this->getModelClass();
        $sqlColumns = $this->getAllColumns($tableAlias, null, true);
        $displayNames = $className::properties();
        $displayNamesWithQuotes = [];
        foreach($displayNames as $displayName) {
            array_push($displayNamesWithQuotes, '`'.$displayName.'`');
        }
        $sqlGenerator = new SQLGenerator();
        $sqlGenerator->select($sqlColumns, $displayNamesWithQuotes);
        $sqlGenerator->from($className::tableName()." $tableAlias");
        return $sqlGenerator;
    }
	
	/**
	 * Returns a modelCollection. It will be empty if there were no results.
     * If buildGetSQL defines any sqlParemeters then a prepared statement is used. Otherwise a basic query is used.
	 * @param IServiceOptions $serviceOptions
	 * @return ICollection
	 */
	public function getList($serviceOptions) {
        $sqlParameterList = new SQLParameterList();
		$modelClassName = $this->getModelClass();
        $listClassName = str_replace('Models', 'Services', $modelClassName).'List';
		$sqlGenerator = $this->buildGetSQL($serviceOptions, $sqlParameterList);
        if ($serviceOptions->limit()->exists()) {
            $sqlGenerator->limit($serviceOptions->limit()->value());
        }
        if ($serviceOptions->skip()->exists() && $serviceOptions->skip()->value() > 0) {
            $sqlGenerator->skip($serviceOptions->skip()->value());
        }
		$sqlQuery = new SQLQuery(SQLConnection::getInstance());
        $sqlQuery->setQuery($sqlGenerator->toSQL())
                 ->setClass($modelClassName);
        if ($sqlParameterList->isEmpty()) {
            $sqlQuery->select();
        } else {
            $sqlQuery->selectSecure($sqlParameterList);
        }
        $results = $sqlQuery->getResults();
        /** @var ICollection $collection */
		$collection = new $listClassName();
		if (!$results->hasResults()) return $collection;
		$collection->add($results->all());
		return $collection;
	}

    /**
     * Pass a model (or array of models) and insert them. If the ID is auto generated this will fill it in after the update occurs
     * @param IModel|IModel[] $models
     */
	public function insert($models) {
	    $this->log->debug('insert models '.json_encode($models));
		$modelsArray = ObjectUtil::ensureArray($models);
		foreach ($modelsArray as $model) {
			$this->insertSingleModel($model);
		}
	}
	
	/**
	 * @param IModel $model
	 * @return boolean
	 */
	protected function insertSingleModel($model) {
		$className = $this->getModelClass();
		$sql = $this->buildInsertSQL();
		$stmt = SQLConnection::getInstance()->prepare($sql);
		$properties = $className::properties();
		$columns = $this->getAllColumns();
		if ($className::hasAutoIncrement()) {
			$properties = $className::nonPrimaryKeyProperties();
			$columns = $this->getNonPrimaryKeyColumns();
		}
		$index = 0;
		foreach ($columns as $column) {
			$property = $properties[$index];
            $getterName = 'get'.ucfirst($property);
            $variable = $model->{$getterName}();
			$stmt->bindValue(":$column", $variable);
			$index++;
		}
		$result = $stmt->execute();
        if ($className::hasAutoIncrement() && count($this->getPrimaryKeyProperties()) === 1) {
            $settingFunction = 'set'.$this->getPrimaryKeyProperties()[0];
            $model->$settingFunction(SQLConnection::getInstance()->lastInsertId());
        }
        return $result;
	}
	
	protected function buildInsertSQL() {
        $className = $this->getModelClass();
		$values = array();
		if ($className::hasAutoIncrement()) {
			$columns = $this->getNonPrimaryKeyColumns();
			foreach ($this->getNonPrimaryKeyColumns() as $nonPrimaryKeyProperty) {
				array_push($values, ":$nonPrimaryKeyProperty");
			}
		} else {
			$columns = $this->getAllColumns();
			foreach ($this->getAllColumns() as $allColumn) {
				array_push($values, ":$allColumn");
			}
		}
		$sql = "insert into ".$className::tableName()." (`".implode('`, `', $columns)."`)
				values (".implode(', ', $values).");";
        $this->log->debug('insert sql = '.$sql);
		return $sql;
	}

    /**
     * Pass a model (or array of models) and update them
     * @param IModel|IModel[] $models
     */
	public function update($models) {
		$modelsArray = ObjectUtil::ensureArray($models);
		foreach ($modelsArray as $model) {
			$this->updateSingleModel($model);
		}
	}
	
	/**
	 * @param IModel $model
	 * @return boolean
	 */
	protected function updateSingleModel($model) {
        $className = $this->getModelClass();
		$sql = $this->buildUpdateSQL();
		$stmt = SQLConnection::getInstance()->prepare($sql);
		$properties = $className::properties();
		$index = 0;
		foreach ($this->getAllColumns() as $column) {
			$property = $properties[$index];
            $getterName = 'get'.ucfirst($property);
            $variable = $model->{$getterName}();
			$stmt->bindValue(":$column", $variable);
			$index++;
		}
		return $stmt->execute();
	}
	
	protected function buildUpdateSQL() {
        $className = $this->getModelClass();
		$setItems = array();
		foreach ($this->getNonPrimaryKeyColumns() as $nonPrimaryKeyProperty) {
			array_push($setItems, "`$nonPrimaryKeyProperty` = :$nonPrimaryKeyProperty");
		}
		$whereItems = array();
		foreach ($this->getPrimaryKeyColumns() as $primaryKeyProperty) {
			array_push($whereItems, "`$primaryKeyProperty` = :$primaryKeyProperty");
		}
		$sql = "update ".$className::tableName()."
				set ".implode(', ', $setItems)."
				where ".implode(' and ', $whereItems).";";
		return $sql;
	}
	
	/**
	 * Pass a model (or array of models) and insert them. If the ID is auto generated this will fill it in after the update occurs
	 * @param IModel|IModel[] $models
	 */
	public function upsert($models) {
		$modelsArray = ObjectUtil::ensureArray($models);
		foreach ($modelsArray as $model) {
			$this->upsertSingleModel($model);
		}
	}
	
	/**
	 * @param IModel $model
	 * @return boolean
	 */
	protected function upsertSingleModel($model) {
        $className = $this->getModelClass();
		$sql = $this->buildUpsertSQL();
		$stmt = SQLConnection::getInstance()->prepare($sql);
		$properties = $className::properties();
		$columns = $this->getAllColumns();
		if ($className::hasAutoIncrement()) {
			$properties = $className::nonPrimaryKeyProperties();
			$columns = $this->getNonPrimaryKeyColumns();
		}
		$index = 0;
		foreach ($columns as $column) {
			$property = $properties[$index];
            $getterName = 'get'.ucfirst($property);
            $variable = $model->{$getterName}();
			$stmt->bindValue(":$column", $variable);
			$index++;
		}
		return $stmt->execute();
	}
	
	protected function buildUpsertSQL() {
        $className = $this->getModelClass();
		$values = array();
		if ($className::hasAutoIncrement()) {
			$columns = $this->getNonPrimaryKeyColumns();
			foreach ($this->getNonPrimaryKeyColumns() as $nonPrimaryKeyProperty) {
				array_push($values, ":$nonPrimaryKeyProperty");
			}
		} else {
			$columns = $this->getAllColumns();
			foreach ($this->getAllColumns() as $allColumn) {
				array_push($values, ":$allColumn");
			}
		}
		$duplicateUpdates = array();
		$nonUniqueColumns = $this->getNonUniqueColumns();
		foreach ($nonUniqueColumns as $nonUniqueColumn) {
			array_push($duplicateUpdates, "`$nonUniqueColumn` = values(`$nonUniqueColumn`)");
		}
		$sql = "insert into ".$className::tableName()." (`".implode('`, `', $columns)."`)
				values (".implode(', ', $values).")
				on duplicate key update ".implode(', ', $duplicateUpdates).";";
		return $sql;
	}	
	
	/**
	 * Pass a model (or array of models) and delete them. This assumes a hard delete. Override in the subclass if it should be a soft-delete instead.
	 * @param IModel|IModel[] $models
	 */
	public function delete($models) {
		$modelsArray = ObjectUtil::ensureArray($models);
		foreach ($modelsArray as $model) {
			$this->deleteSingleModel($model);
		}
	}
	
	/**
	 * @param IModel $model
	 * @return boolean
	 */
	protected function deleteSingleModel($model) {
        $className = $this->getModelClass();
		$sql = $this->buildDeleteSQL();
		$stmt = SQLConnection::getInstance()->prepare($sql);
		$properties = $className::primaryKeyProperties();
		$index = 0;
		foreach ($this->getPrimaryKeyColumns() as $column) {
			$property = $properties[$index];
            $getterName = 'get'.ucfirst($property);
            $variable = $model->{$getterName}();
            $stmt->bindValue(":$column", $variable);
			$index++;
		}
		return $stmt->execute();
	}
	
	protected function buildDeleteSQL() {
        $className = $this->getModelClass();
		$whereItems = array();
		foreach ($this->getPrimaryKeyColumns() as $primaryKeyProperty) {
			array_push($whereItems, "`$primaryKeyProperty` = :$primaryKeyProperty");
		}
		$sql = "delete from ".$className::tableName()." where ".implode(' and ', $whereItems).";";
		return $sql;
	}
	
	protected function getPropertiesOfType($type) {
        $className = $this->getModelClass();
		return $className::$type();
	}
	
	protected function getPrimaryKeyProperties($blackListedProperties = null) {
		return $this->filterItems($this->getPropertiesOfType('primaryKeyProperties'), $blackListedProperties);
	}
	
	protected function getNonPrimaryKeyProperties($blackListedProperties = null) {
		return $this->filterItems($this->getPropertiesOfType('nonPrimaryKeyProperties'), $blackListedProperties);
	}
	
	protected function getAllProperties($blackListedProperties = null) {
		return $this->filterItems($this->getPropertiesOfType('properties'), $blackListedProperties);
	}
	
	protected function getNonUniqueProperties($blackListedProperties = null) {
		return $this->filterItems($this->getPropertiesOfType('nonUniqueProperties'), $blackListedProperties);
	}

	protected function getPrimaryKeyColumns($tableAlias = null, $blackListedProperties = null) {
		return $this->convertPropertiesToSqlCase($this->getPrimaryKeyProperties($blackListedProperties), $tableAlias);
	}
	
	protected function getNonPrimaryKeyColumns($tableAlias = null, $blackListedProperties = null) {
		return $this->convertPropertiesToSqlCase($this->getNonPrimaryKeyProperties($blackListedProperties), $tableAlias);
	}

    /**
     * @param string $tableAlias Optional. Pass if you want to add the table alias as a prefix like a.column. Defaults to null
     * @param string[] $blackListedProperties Optional. Pass if you want to omit specific columns. Defaults to null
     * @param bool $surroundWithQuotes Optional. Pass true if you want to surround the columns with double quotes to escape reserved words causing issues. Defaults to false
     * @return array
     */
	protected function getAllColumns($tableAlias = null, $blackListedProperties = null, $surroundWithQuotes = false) {
		return $this->convertPropertiesToSqlCase($this->getAllProperties($blackListedProperties), $tableAlias, $surroundWithQuotes);
	}
	
	protected function getNonUniqueColumns($tableAlias = null, $blackListedProperties = null) {
		return $this->convertPropertiesToSqlCase($this->getNonUniqueProperties($blackListedProperties), $tableAlias);
	}
	
	protected function filterItems($items, $blackListedProperties = null) {
		if (!isset($blackListedProperties)) return $items;
		return Arrays::without($items, $blackListedProperties);
	}

    /**
     * Turns properties into their sql string version to create column names for sql.
     * @param string[] $properties
     * @param string $tableAlias Optional. Pass if you want to add the table alias as a prefix like a.column. Defaults to null
     * @param bool $surroundWithQuotes Optional. Pass true if you want to surround the columns with double quotes to escape reserved words causing issues. Defaults to false
     * @return string[]
     */
	protected function convertPropertiesToSqlCase($properties, $tableAlias = null, $surroundWithQuotes = false) {
		$prefix = (isset($tableAlias)) ? "$tableAlias." : '' ;
		$convertedProperties = array();
		foreach ($properties as $property) {
		    if ($surroundWithQuotes) {
                $convertedProperty = $prefix.'`'.StringUtil::convertTitleToUnderscoreCase($property).'`';
            } else {
                $convertedProperty = $prefix.StringUtil::convertTitleToUnderscoreCase($property);
            }
			array_push($convertedProperties, $convertedProperty);
		}
		return $convertedProperties;
	}
	
}