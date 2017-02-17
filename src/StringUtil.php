<?php

namespace GoferUtil;

/**
 * Utility class for common string functions
 */
class StringUtil {

    /**
     * A regex string to match a uuid 4 like 00000000-0000-0000-0000-000000000000
     * The dashes are optional with this pattern
     */
    const REGEX_UUID4 = '[a-f0-9]{8}-?[a-f0-9]{4}-?[a-f0-9]{4}-?[a-f0-9]{4}-?[a-f0-9]{12}';

	/**
	 * If a word is found at the end it will remove that word. It will also trim the string
     * If multiple are passed it will only remove the first one it finds
	 * @param string $wordToRemove A word or array of words to remove if found
	 * @param string $subject
	 * @return string
	 */
    public static function removeWordFromEnd($wordToRemove, $subject) {
        if (!is_array($wordToRemove)) {
            $wordToRemove = [$wordToRemove];
        }
        $subject = trim($subject);
        $subjectBefore = $subject;
        foreach ($wordToRemove as $word) {
            $subject = trim(preg_replace('/'. preg_quote($word, '/') . '$/', '', $subject));
            if ($subject !== $subjectBefore) {
                return trim($subject);
            }
        }
        return $subject;
    }

    /**
     * Returns an array where index 0 = first name, 1 = last name
     * Returned results are trimmed of whitespace
     * If no first/last name can be found then the full name is assigned to the first name and last name is an empty string.
     * @param string $fullName
     * @return array
     */
    public static function extractFirstLastNameFromFull($fullName) {
        $fullName = trim($fullName);
        if (!strpos($fullName," ",0)) {
            return array($fullName, "");
        }
        $parts = explode(" ", $fullName);
        $lastName = array_pop($parts);
        $firstName = implode(" ", $parts);
        return array(trim($firstName), trim($lastName));
    }

    /**
     * Replaces underscores with spaces or whatever character you specify
     * str_replace("_", " ", $word)
     * @param string $word
     * @param string $replacement Optional. The replacement character. Default is a space
     * @return mixed
     */
    public static function replaceUnderscores($word, $replacement = ' ') {
        return str_replace("_", $replacement, $word);
    }
    
    /**
     * Converts the first letter of each word to uppercase
     * @param string $word
     * @param boolean $includeUnderscore Optional If true then it will also treat _ like spaces. Default false
     * @return string
     */
    public static function convertToTitleCase($word, $includeUnderscore = false) {
    	$delimiters = ($includeUnderscore) ? "_ \t\r\n\f\v" : " \t\r\n\f\v";
        return ucwords($word, $delimiters);
    }
    
    /**
     * Converts the first letter of each word to uppercase except the first letter of the whole string.
     * Any spaces between words are removed.
     * @param string $word
     * @return string
     */
    public static function convertToCamelCase($word) {
    	$word = StringUtil::convertToTitleCase($word, true);
        $word = str_replace('_', '',$word);
        $word = str_replace(' ', '',$word);
    	return lcfirst($word);
    }

    /**
     * Converts a word with spaces into underscore case.
     * Word is also converted to all lowercase.
     * str_replace(" ", "_", strtolower($word))
     * @param string $word
     * @return string
     */
    public static function convertToUnderscoreCase($word) {
    	return str_replace(" ", "_", strtolower($word));
    }

    /**
     * Converts a word that is in TitleCase already to underscore case
     * The whole returned word will be in lowercase.
     * @param string $word
     * @return string
     */
    public static function convertTitleToUnderscoreCase($word) {
    	return strtolower(substr($word,0,1).preg_replace('/[A-Z]/', '_$0', substr($word, 1)));
    }

    /**
     * Converts a string amount with commas and a $ sign to an integer value
     * $1,000,000 becomes 1000000
     * @param string $amount
     * @return int
     */
    public static function convertAmountToInteger($amount) {
    	return intval(str_replace(",", "", str_replace("$", "", $amount)));
    }

    /**
     * Takes a string with urls in it and turns them into <a href=''></a> tags
     * @param string $string
     * @return string
     */
    public static function turnUrlsIntoHtmlLinks($string) {
        // (?<!["|>]) means it cannot have a double quote or a > before it - meaning it is probably already inside an anchor tag
    	$url = '~(?<!["|>])(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
    	$string = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $string);
    	return $string;
    }

    /**
     * Takes html and strips all the tags out of it such as html links and turns them back into url strings.
     * In the background it is really calling strip_tags()
     * @param string $string
     * @return string
     */
    public static function turnHtmlLinksIntoUrlStrings($string) {
    	return strip_tags($string);
    }

    /**
     * Takes email text and converts it to html for use in an email.
     * All content separated by line breaks are converted to <div>Content</div>
     * 2 line breaks in a row are converted to <div><br></div>
     * Make sure to call htmlspecialchars first to escape bad characters
     * @param string $text
     * @return string
     */
    public static function convertEmailTextToHTML($text) {
    	$parts = explode ( PHP_EOL, $text);
    	$replyHTML = "";
    	foreach ( $parts as $part ) {
    		$replyHTML .= "<div>" . $part . "</div>";
    	}
    	$replyHTML = str_replace ( "<div></div><div></div>", "<div><br></div>", $replyHTML ); // changing double line breaks to be a br since two divs in a row look like just one div in html
    	return $replyHTML;
    }

    /**
     * Takes 2 double spaces and returns a single space
     * str_replace("  ", " ", $string)
     * @param string $string
     * @return string
     */
    public static function stripDoubleSpaces($string) {
    	return str_replace("  ", " ", $string);
    }

    /**
     * Decodes any html characters in a string back to their real text equivalents
     * html_decode_entity was not working on this numeric reference but this extra function seems to fix that
     * For example - I&#39;ve CC&#39;d my excellent assistant Gofer - the &#39; was not decoding
     * @param string $string
     * @param string $encoding Optional default UTF-8
     * @param bool $fixMappingBug Optional default = true
     * @return mixed
     */
    public static function html_character_reference_decode($string, $encoding='UTF-8', $fixMappingBug=true) {
    	$reference = function($match) use ($encoding, $fixMappingBug) {
    		if (strtolower($match[1][0]) === "x") {
    			$codepoint = intval(substr($match[1], 1), 16);
    		} else {
    			$codepoint = intval($match[1], 10);
    		}
    		// @see http://www.cs.tut.fi/~jkorpela/www/windows-chars.html
    		if ($fixMappingBug && $codepoint >= 130 && $codepoint <= 159) {
    			$mapping = array(
    					8218, 402, 8222, 8230, 8224, 8225, 710, 8240, 352, 8249,
    					338, 141, 142, 143, 144, 8216, 8217, 8220, 8221, 8226,
    					8211, 8212, 732, 8482, 353, 8250, 339, 157, 158, 376);
    			$codepoint = $mapping[$codepoint-130];
    		}
    		return mb_convert_encoding(pack("N", $codepoint), $encoding, "UTF-32BE");
    	};
    	return preg_replace_callback('/&#(x[0-9a-f]+|[0-9]+);/i', $reference, $string);
    }
    
    public static function extractTextFromHTML($html) {
    	$decoded = StringUtil::html_character_reference_decode(html_entity_decode(strip_tags($html)));
    	return preg_replace( "/\n\s+/", "\n", rtrim($decoded) );
    }

    /**
     * Returns an array of the starting indexes for ALL needles found in the haystack. Like strpos but for all
     * @param string $needle
     * @param string $haystack
     * @return array
     */
    public static function strpos_all($needle, $haystack) {
		$offset = 0;
		$allPositions = array();
		while (($position = strpos($haystack, $needle, $offset)) !== FALSE) {
			$offset = $position + 1;
			array_push($allPositions, $position);
		}
		return $allPositions;
	}
	
	/**
	 * Converts a word that is plural to a singular equivalent
	 * @param string $word
	 * @return string
	 */
	public static function pluralToSingular($word) {
        $wordLowerCase = strtolower($word);
        if (static::isPluralEndingToIgnore($word)) {
            return $word;
        } elseif (strpos($wordLowerCase,"ies") >= 1) {
			return (substr($wordLowerCase, -3) === "ies") ? substr($word, 0, -3)."y" : rtrim($word, "s") ;
		} elseif (strpos($wordLowerCase,"ches") >= 1) {
            return (substr($wordLowerCase, -4) === "ches") ? substr($word, 0, -4)."ch" : rtrim($word, "s") ;
        } else {
			return rtrim($word, "s") ;
		}
	}

    /**
     * Checks if a word is a plural version or not
     * @param string $word
     * @return bool
     */
	public static function isPluralWord($word) {
        $wordLowerCase = strtolower($word);
        if (static::isPluralEndingToIgnore($word)) {
            return false;
        } elseif (strpos($wordLowerCase,"ies") >= 1) {
            return true;
        } elseif (strpos($wordLowerCase,"ches") >= 1) {
            return true;
        } elseif (static::endsWith($word, 's')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a word is a singular version or not
     * Alias for isPluralWord reversed
     * @param string $word
     * @return bool
     */
    public static function isSingularWord($word) {
        return !static::isPluralWord($word);
    }

    /**
     * Checks if it is an ending that should not be converted to a plural equivalent
     * Add more entries to the array as more instances come up that need to be ignored.
     * Check github if you need to see a bigger list of other words.
     * @param string $word
     * @return bool
     */
	protected static function isPluralEndingToIgnore($word) {
        $wordLowerCase = strtolower($word);
        $ignoreEndingList = ['always', 'plus'];
        foreach ($ignoreEndingList as $ending) {
            $length = strlen($ending);
            $negativeLength = 0 - $length;
            if (strlen($wordLowerCase) < $length) continue;
            if (substr($wordLowerCase, $negativeLength) === $ending) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the last characters of a string. Defaults to last 1
     * Returns the whole word if there aren't enough characters as requested.
     * @param string $word
     * @param int $numberOfCharacters Optional. Defaults to 1
     * @return string
     */
    public static function last($word, $numberOfCharacters = 1) {
        if (strlen($word) < $numberOfCharacters) return $word;
        $lastN = 0 - $numberOfCharacters;
        return substr($word, $lastN);
    }

    /**
     * Returns the first characters of a string. Defaults to first 1 character.
     * Returns the whole word if there aren't enough characters as requested.
     * @param string $word
     * @param int $numberOfCharacters Optional. Defaults to 1
     * @return string
     */
    public static function first($word, $numberOfCharacters = 1) {
        if (strlen($word) < $numberOfCharacters) return $word;
        return substr($word, 0, $numberOfCharacters);
    }

    /**
     * Returns all the characters AFTER the LAST occurence of the character is found
     * If the search character is not found or is already at the end then it returns the whole string
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function afterLast($subject, $search) {
        if (strpos($subject, $search) === false) return $subject;
        if (strpos($subject, $search) === strlen($subject)-1) return $subject; //character is already at the end
        return substr($subject, strrpos($subject, $search) + 1);
    }

    /**
     * Returns all the characters BEFORE the FIRST occurence of the character is found
     * If the search character is not found or is at the start then it returns the whole string
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function beforeFirst($subject, $search) {
        if (strpos($subject, $search) === false) return $subject;
        if (strpos($subject, $search) === 0) return $subject; //character is already at the start
        return substr($subject, 0, strrpos($subject, $search));
    }

    /**
     * Returns true if the subject starts with the search string. CASE SENSITIVE
     * @param string $subject The subject phrase to search inside
     * @param string $search The term to search for at the start
     * @return bool
     */
    public static function startsWith($subject, $search) {
        if (strlen($subject) < strlen($search)) return false;
        return substr($subject, 0, strlen($search)) === $search;
    }

    /**
     * Returns true if the subject ends with the search string. CASE SENSITIVE by default
     * @param string $subject The subject phrase to search inside
     * @param string $search The term to search for at the end
     * @param bool $caseSensitive Optional. Default = true
     * @return bool
     */
    public static function endsWith($subject, $search, $caseSensitive = true) {
        if (strlen($subject) < strlen($search)) return false;
        if (!$caseSensitive) {
            $subject = strtolower($subject);
            $search = strtolower($search);
        }
        $start = strlen($subject)-strlen($search);
        return substr($subject, $start, strlen($search)) === $search;
    }

    /**
     * Checks if any of the patterns exist in the subject string. Returns true if at least 1 pattern exists.
     * Pass in any flags after the last slash like with normal preg_match function:
     * i case insensitive 
     * m treat as multi-line string 
     * s dot matches newline 
     * x ignore whitespace in regex  A matches only at the start of string 
     * D matches only at the end of string 
     * U non-greedy matching by default
     * @param string $subject The subject phrase to search inside
     * @param string|string[] $patterns A regex pattern (or list of patterns) to search for. Strings must start and end with / like /pattern/
     * @return bool
     */
    public static function matchesPattern($subject, $patterns) {
        $patterns = ObjectUtil::ensureArray($patterns);
        foreach($patterns as $pattern) {
            if (preg_match($pattern, $subject) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the first capturing group from this first pattern that matches in the subject.
     * The capture group is like normal regex with parenthesis.
     * If you need a capture group in your regex that is checked but is not returned then put ?: at the front like: (?:non capturing group)
     * Returns the string if found or false if no match. Use matchesPattern first if you need to check beforehand
     * @see matchesPattern for a list of flags to use
     * @param string $subject The subject phrase to search inside
     * @param string|string[] $patterns A regex pattern (or list of patterns) to search for. Strings must start and end with / like /pattern/
     * @return string|false
     */
    public static function getMatchedPattern($subject, $patterns) {
        $matches = [];
        $patterns = ObjectUtil::ensureArray($patterns);
        foreach($patterns as $pattern) {
            if (preg_match($pattern, $subject, $matches) === 1) {
                if (count($matches) >= 2) {
                    return $matches[1]; //the first match is the whole string... the second is the first matching group
                }
            }
        }
        return false;
    }

    /**
     * Pass a string and this will return just the numbers as a string.
     * If no numbers found then an empty string is returned.
     * Only absolute numbers are returned. Minus signs ignored.
     * @param string $subject
     * @return string
     */
    public static function getIntegers($subject) {
        $numbers =  filter_var(str_replace('.','',str_replace('+','',str_replace('-', '', $subject))), FILTER_SANITIZE_NUMBER_INT);
        if (strlen($numbers) === 0) return '';
        return $numbers;
    }

    /**
     * Replaces all \r\n and \r line breaks with just \n
     * @param string $subject
     * @return string
     */
    public static function convertNewLinesToUnixNewLines($subject) {
        if (empty($subject)) return $subject;
        return preg_replace('/\r\n?/', PHP_EOL, $subject);
    }

    /**
     * Returns true if the string has special characters that will get cleaned up when calling 'escape' like <>"&'
     * @param $source
     * @return bool
     */
    public static function hasSpecialCharacters($source) {
        return preg_match('/[<>"&\']/i', $source) === 1;
    }

    /**
     * Escape all html, javascript, css in a string to prevent XSS attacks.
     * Should be applied before outputting data back to the user
     * @param string $source
     * @param bool $useHTML Optional. Set to true and all characters will be forced to their html escaped equivalent. Default = false
     * @param string $encoding
     * @return string
     */
    public static function escape($source, $useHTML = false, $encoding = 'UTF-8') {
        if (!isset($source)) return $source;
        if ($useHTML) {
            return htmlentities($source, ENT_QUOTES | ENT_HTML5, $encoding);
        }
        return htmlspecialchars($source, ENT_QUOTES | ENT_HTML5, $encoding);
    }

    /**
     * Sanitize string removing all html, javascript, css to prevent XSS attacks.
     * Should be applied before inserting data to the database
     * @param string $source
     * @param string $encoding
     * @return string
     */
    public static function sanitize($source, $encoding = 'UTF-8') {
        if (!isset($source)) return $source;
        return strip_tags($source, $encoding);
    }

}