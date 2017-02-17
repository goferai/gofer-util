<?php

namespace GoferUtil;

class ArrayUtil {

    /**
     * Pass an array of strings that you want allowed. All other strings are removed.
     * Returns a new array (array keys are reset to start at 0)
     * NOTE: This IS case sensitive - strings must match exactly
     * @param string|string[] $strings List of strings to filter for
     * @param string[] $subject The subject list of strings that will be altered
     * @return string[]
     */
    public static function filterOnlyStrings($strings, $subject) {
        if (!is_array($strings)) {
            $strings = [$strings];
        }
        return array_values(array_intersect($subject, $strings));
    }

	/**
	 * Pass an array of strings that you want removed from a list.
     * Returns a new array with the strings removed (array keys are reset to start at 0)
     * NOTE: This IS case sensitive - strings must match exactly
	 * @param string|string[] $strings List of strings to remove
	 * @param string[] $subject The subject list of strings that will be altered
	 * @return string[]
	 */
    public static function removeStrings($strings, $subject) {
        if (!is_array($strings)) {
            $strings = [$strings];
        }
        return array_values(array_diff($subject, $strings));
    }

    /**
     * Pass an array of strings and it will remove any entries that match that prefix
     * Returns a new array with the strings removed (array keys are reset to start at 0)
     * NOTE: This IS case sensitive - strings must match exactly
     * @param string|string[] $prefixes List of prefixes to check for
     * @param string[] $subject The subject list of strings that will be altered
     * @return string[]
     */
    public static function removeStringsWithPrefix($prefixes, $subject) {
        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }
        foreach ($prefixes as $prefix) {
            $subject = array_filter($subject, function($item) use ($prefix) {
                return !StringUtil::startsWith($item, $prefix);
            });
        }
        return array_values($subject);
    }

    /**
     * Pass an array of strings and a suffix and the suffix will get added to the end each entry
     * Returns a new array
     * @param string $suffix String to add to the end
     * @param string[] $subject The subject list of strings that will be altered
     * @return string[]
     */
    public static function addSuffix($suffix, $subject) {
        $newArray = [];
        foreach($subject as $key=>$value) {
            $newArray[$key] = $value.$suffix;
        }
        return $newArray;
    }

}