<?php

namespace GoferUtil;

class NumberUtil {

    /**
     * Pass in a percent from 0 to 100 and this will return it into an integer between 0 and 10.
     * Example: 0 -> 0, 9 -> 0, 10 -> 1, 89 -> 8, 90 -> 9, 99 -> 9, 100 -> 10
     * @param float $percent
     * @return float
     */
	public static function convertPercentToRange10($percent) {
		return floor($percent / 10);
	}

    /**
     * Checks if the passed in variable is an integer and is between the start and end values (including start and end)
     * Returns false if not an integer value or not between the start end
     * Note that you can pass a number like 2.0 but passing '2.0' as a string will not work. However you can pass just '2' as a string.
     * @param mixed $value The value to check - can be a string or a number
     * @param int $start
     * @param int $end
     * @return bool
     */
	public static function isIntegerBetween($value, $start, $end) {
	    if (filter_var($value, FILTER_VALIDATE_INT) === false) return false;
	    return intval($value) >= $start && intval($value) <= $end;
    }
	
}