<?php

namespace GoferUtil;

class JsonUtil {
    
	public static function jsonDecodeToObject($json, $object) {
		foreach (json_decode($json) as $key => $value) {
			$object->{$key} = $value;
		}
		return $object;
	}
    
	public static function convertStdClassToObject($data, $object) {
		foreach ($data as $key => $value) {
			$object->{$key} = $value;
		}
		return $object;
	}
	
	/**
	 * Pass in some json and this will return an html table of the results (even sub results)
	 * @param string $json
	 * @return string
	 */
	public static function toHTMLTable($json) {
		$rows = json_decode($json);
		if (!is_array($rows)) {
			$rows = array($rows);
		}
		return self::generateHTMLTable($rows);
	}
	
	private function generateHTMLTable($object) {
		$html = "";
		if (is_object($object)) {
			$html = "";
			foreach ($object as $propertyValue) {
				$html .= "<td>".self::generateHTMLTable($propertyValue)."</td>";
			}
			return $html;
		} elseif(is_array($object) && count($object) >= 1) {
			if (is_object($object[0])) {
				$html = self::convertObjectsToHTMLTable($object);
			}
		} elseif (is_string($object)) {
			$html = $object;
		} elseif (is_numeric($object)) {
			$html = strval($object);
		} elseif (is_bool($object)) {
			$html = ($object) ? 'Y' : 'N' ;
		} else {
			$html = $object;
		}
		return $html;
	}
	
	private function convertObjectsToHTMLTable($rows) {
		$html = "<table class='table'>";
		$columnNames = get_object_vars($rows[0]);
		$html .= "<thead><tr>";
		foreach(array_keys($columnNames) as $key) {
			$html .= "<th>".StringUtil::convertToTitleCase(StringUtil::replaceUnderscores($key))."</th>";
		}
		$html .= "</tr></thead>";
		$html .= "<tbody>";
		foreach ($rows as $row) {
			$html .= "<tr>";
			$html .= self::generateHTMLTable($row);
			$html .= "</tr>";
		}
		$html .="</tbody></table>";
		return $html;
	}
	
}