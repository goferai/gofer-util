<?php

namespace GoferUtil;

class DateUtil {

    /**
     * Shortcut for 2004-02-12T15:19:21+00:00
     */
	const FORMAT_ISO8601 = 'c';

    /**
     * 'D, M j, Y, g:i A' like Mon, Jan 1, 2016, 1:00 AM
     */
	const FORMAT_DATE_TIME_DDD_MMM_D_YYYY_H_MM_AM = 'D, M j, Y, g:i A';

    /**
     * 'D, M j' like Mon, Jan 1, 2016
     * (No leading zeros on day)
     */
    const FORMAT_DATE_DDD_MMM_D_YYYY = 'D, M j, Y';

    /**
     * 'D, M j' like Mon, Jan 1
     * (No leading zeros on day)
     */
	const FORMAT_DATE_DDD_MMM_D = 'D, M j';

    /**
     * 'g:i A' like 1:00 AM
     * (No leading zeros on hour)
     * (12-hour format)
     */
	const FORMAT_TIME_H_MM_AM = 'g:i A';

    /**
     * 'Y-m-d' like 16-01-01
     * (With leading zeros)
     */
	const FORMAT_DATE_YYYY_MM_DD = 'Y-m-d';

    /**
     * 'n/j/y' like 1/1/16
     * (No leading zeros)
     */
    const FORMAT_DATE_M_D_YY = 'n/j/y';

    /**
     * 'n/j/y g:i A' like 1/1/16 1:00 AM
     * (No leading zeros)
     * (12-hour format)
     */
    const FORMAT_DATE_TIME_M_D_YY_H_MM_AM = 'n/j/y g:i A';

    /**
     * 'm/d/Y' like 01/01/2016
     * (With leading zeros)
     */
	const FORMAT_DATE_MM_DD_YYYY = 'm/d/Y';
	
	/**
	 * 'Y-m-d H:i:s' like 2016-01-21 23:00:00
	 * (With leading zeros)
     * (24-hours Format)
	 */
	const FORMAT_DATE_YYYY_MM_DD_HH24_M_S = 'Y-m-d H:i:s';
	
	const TZ_UTC = 'Etc/UTC';
    const TZ_PST = 'America/Los_Angeles';

    /**
     * List of time zones to their common names like America/Los_Angeles becomes Pacific Time
     * Timezones are listed from west to east
     */
    const TZ_TO_NAME_MAP = [
        'Pacific/Niue' => 'Niue',
        'Pacific/Pago_Pago' => 'Pago Pago',
        'Pacific/Honolulu' => 'Hawaii Time',
        'Pacific/Rarotonga' => 'Rarotonga',
        'Pacific/Tahiti' => 'Tahiti',
        'Pacific/Marquesas' => 'Marquesas',
        'America/Anchorage' => 'Alaska Time',
        'Pacific/Gambier' => 'Gambier',
        'America/Los_Angeles' => 'Pacific Time',
        'America/Tijuana' => 'Pacific Time - Tijuana',
        'America/Vancouver' => 'Pacific Time - Vancouver',
        'America/Whitehorse' => 'Pacific Time - Whitehorse',
        'Pacific/Pitcairn' => 'Pitcairn',
        'America/Dawson_Creek' => 'Mountain Time - Dawson Creek',
        'America/Denver' => 'Mountain Time',
        'America/Edmonton' => 'Mountain Time - Edmonton',
        'America/Hermosillo' => 'Mountain Time - Hermosillo',
        'America/Mazatlan' => 'Mountain Time - Chihuahua, Mazatlan',
        'America/Phoenix' => 'Mountain Time - Arizona',
        'America/Yellowknife' => 'Mountain Time - Yellowknife',
        'America/Belize' => 'Belize',
        'America/Chicago' => 'Central Time',
        'America/Costa_Rica' => 'Costa Rica',
        'America/El_Salvador' => 'El Salvador',
        'America/Guatemala' => 'Guatemala',
        'America/Managua' => 'Managua',
        'America/Mexico_City' => 'Central Time - Mexico City',
        'America/Regina' => 'Central Time - Regina',
        'America/Tegucigalpa' => 'Central Time - Tegucigalpa',
        'America/Winnipeg' => 'Central Time - Winnipeg',
        'Pacific/Easter' => 'Easter Island',
        'Pacific/Galapagos' => 'Galapagos',
        'America/Bogota' => 'Bogota',
        'America/Cancun' => 'America Cancun',
        'America/Guayaquil' => 'Guayaquil',
        'America/Havana' => 'Havana',
        'America/Iqaluit' => 'Eastern Time - Iqaluit',
        'America/Jamaica' => 'Jamaica',
        'America/Lima' => 'Lima',
        'America/Nassau' => 'Nassau',
        'America/New_York' => 'Eastern Time',
        'America/Panama' => 'Panama',
        'America/Port-au-Prince' => 'Port-au-Prince',
        'America/Rio_Branco' => 'Rio Branco',
        'America/Toronto' => 'Eastern Time - Toronto',
        'America/Asuncion' => 'Asuncion',
        'America/Barbados' => 'Barbados',
        'America/Boa_Vista' => 'Boa Vista',
        'America/Campo_Grande' => 'Campo Grande',
        'America/Caracas' => 'Caracas',
        'America/Cuiaba' => 'Cuiaba',
        'America/Curacao' => 'Curacao',
        'America/Grand_Turk' => 'Grand Turk',
        'America/Guyana' => 'Guyana',
        'America/Halifax' => 'Atlantic Time - Halifax',
        'America/La_Paz' => 'La Paz',
        'America/Manaus' => 'Manaus',
        'America/Martinique' => 'Martinique',
        'America/Port_of_Spain' => 'Port of Spain',
        'America/Porto_Velho' => 'Porto Velho',
        'America/Puerto_Rico' => 'Puerto Rico',
        'America/Santiago' => 'Santiago',
        'America/Santo_Domingo' => 'Santo Domingo',
        'America/Thule' => 'Thule',
        'Antarctica/Palmer' => 'Palmer',
        'Atlantic/Bermuda' => 'Bermuda',
        'America/St_Johns' => 'Newfoundland Time - St. Johns',
        'America/Araguaina' => 'Araguaina',
        'America/Argentina/Buenos_Aires' => 'Buenos Aires',
        'America/Bahia' => 'Salvador',
        'America/Belem' => 'Belem',
        'America/Cayenne' => 'Cayenne',
        'America/Fortaleza' => 'Fortaleza',
        'America/Godthab' => 'Godthab',
        'America/Maceio' => 'Maceio',
        'America/Miquelon' => 'Miquelon',
        'America/Montevideo' => 'Montevideo',
        'America/Paramaribo' => 'Paramaribo',
        'America/Recife' => 'Recife',
        'America/Sao_Paulo' => 'Sao Paulo',
        'Antarctica/Rothera' => 'Rothera',
        'Atlantic/Stanley' => 'Stanley',
        'America/Noronha' => 'Noronha',
        'Atlantic/South_Georgia' => 'South Georgia',
        'America/Scoresbysund' => 'Scoresbysund',
        'Atlantic/Azores' => 'Azores',
        'Atlantic/Cape_Verde' => 'Cape Verde',
        'Africa/Abidjan' => 'Abidjan',
        'Africa/Accra' => 'Accra',
        'Africa/Bissau' => 'Bissau',
        'Africa/Casablanca' => 'Casablanca',
        'Africa/El_Aaiun' => 'El Aaiun',
        'Africa/Monrovia' => 'Monrovia',
        'America/Danmarkshavn' => 'Danmarkshavn',
        'Atlantic/Canary' => 'Canary Islands',
        'Atlantic/Faroe' => 'Faeroe',
        'Atlantic/Reykjavik' => 'Reykjavik',
        'Etc/GMT' => 'GMT (no daylight saving)',
        'Europe/Dublin' => 'Dublin',
        'Europe/Lisbon' => 'Lisbon',
        'Europe/London' => 'London',
        'Africa/Algiers' => 'Algiers',
        'Africa/Ceuta' => 'Ceuta',
        'Africa/Lagos' => 'Lagos',
        'Africa/Ndjamena' => 'Ndjamena',
        'Africa/Tunis' => 'Tunis',
        'Africa/Windhoek' => 'Windhoek',
        'Europe/Amsterdam' => 'Amsterdam',
        'Europe/Andorra' => 'Andorra',
        'Europe/Belgrade' => 'Central European Time - Belgrade',
        'Europe/Berlin' => 'Berlin',
        'Europe/Brussels' => 'Brussels',
        'Europe/Budapest' => 'Budapest',
        'Europe/Copenhagen' => 'Copenhagen',
        'Europe/Gibraltar' => 'Gibraltar',
        'Europe/Luxembourg' => 'Luxembourg',
        'Europe/Madrid' => 'Madrid',
        'Europe/Malta' => 'Malta',
        'Europe/Monaco' => 'Monaco',
        'Europe/Oslo' => 'Oslo',
        'Europe/Paris' => 'Paris',
        'Europe/Prague' => 'Central European Time - Prague',
        'Europe/Rome' => 'Rome',
        'Europe/Stockholm' => 'Stockholm',
        'Europe/Tirane' => 'Tirane',
        'Europe/Vienna' => 'Vienna',
        'Europe/Warsaw' => 'Warsaw',
        'Europe/Zurich' => 'Zurich',
        'Africa/Cairo' => 'Cairo',
        'Africa/Johannesburg' => 'Johannesburg',
        'Africa/Maputo' => 'Maputo',
        'Africa/Tripoli' => 'Tripoli',
        'Asia/Amman' => 'Amman',
        'Asia/Beirut' => 'Beirut',
        'Asia/Damascus' => 'Damascus',
        'Asia/Gaza' => 'Gaza',
        'Asia/Jerusalem' => 'Jerusalem',
        'Asia/Nicosia' => 'Nicosia',
        'Europe/Athens' => 'Athens',
        'Europe/Bucharest' => 'Bucharest',
        'Europe/Chisinau' => 'Chisinau',
        'Europe/Helsinki' => 'Helsinki',
        'Europe/Kaliningrad' => 'Moscow-01 - Kaliningrad',
        'Europe/Kiev' => 'Kiev',
        'Europe/Riga' => 'Riga',
        'Europe/Sofia' => 'Sofia',
        'Europe/Tallinn' => 'Tallinn',
        'Europe/Vilnius' => 'Vilnius',
        'Africa/Khartoum' => 'Khartoum',
        'Africa/Nairobi' => 'Nairobi',
        'Antarctica/Syowa' => 'Syowa',
        'Asia/Baghdad' => 'Baghdad',
        'Asia/Qatar' => 'Qatar',
        'Asia/Riyadh' => 'Riyadh',
        'Europe/Istanbul' => 'Istanbul',
        'Europe/Minsk' => 'Minsk',
        'Europe/Moscow' => 'Moscow+00 - Moscow',
        'Asia/Tehran' => 'Tehran',
        'Asia/Aqtau' => 'Aqtau',
        'Asia/Baku' => 'Baku',
        'Asia/Dubai' => 'Dubai',
        'Asia/Tbilisi' => 'Tbilisi',
        'Asia/Yerevan' => 'Yerevan',
        'Europe/Samara' => 'Moscow+01 - Samara',
        'Indian/Mahe' => 'Mahe',
        'Indian/Mauritius' => 'Mauritius',
        'Indian/Reunion' => 'Reunion',
        'Asia/Kabul' => 'Kabul',
        'Antarctica/Mawson' => 'Mawson',
        'Asia/Aqtobe' => 'Aqtobe',
        'Asia/Ashgabat' => 'Ashgabat',
        'Asia/Bishkek' => 'Bishkek',
        'Asia/Dushanbe' => 'Dushanbe',
        'Asia/Karachi' => 'Karachi',
        'Asia/Tashkent' => 'Tashkent',
        'Asia/Yekaterinburg' => 'Moscow+02 - Yekaterinburg',
        'Indian/Kerguelen' => 'Kerguelen',
        'Indian/Maldives' => 'Maldives',
        'Asia/Calcutta' => 'India Standard Time',
        'Asia/Colombo' => 'Colombo',
        'Asia/Katmandu' => 'Katmandu',
        'Antarctica/Vostok' => 'Vostok',
        'Asia/Almaty' => 'Almaty',
        'Asia/Dhaka' => 'Dhaka',
        'Asia/Omsk' => 'Moscow+03 - Omsk',
        'Asia/Thimphu' => 'Thimphu',
        'Indian/Chagos' => 'Chagos',
        'Asia/Yangon' => 'Rangoon',
        'Indian/Cocos' => 'Cocos',
        'Antarctica/Davis' => 'Davis',
        'Asia/Bangkok' => 'Bangkok',
        'Asia/Hovd' => 'Hovd',
        'Asia/Jakarta' => 'Jakarta',
        'Asia/Krasnoyarsk' => 'Moscow+04 - Krasnoyarsk',
        'Asia/Saigon' => 'Hanoi',
        'Indian/Christmas' => 'Christmas',
        'Asia/Brunei' => 'Brunei',
        'Asia/Choibalsan' => 'Choibalsan',
        'Asia/Hong_Kong' => 'Hong Kong',
        'Asia/Irkutsk' => 'Moscow+05 - Irkutsk',
        'Asia/Kuala_Lumpur' => 'Kuala Lumpur',
        'Asia/Macau' => 'Macau',
        'Asia/Makassar' => 'Makassar',
        'Asia/Manila' => 'Manila',
        'Asia/Shanghai' => 'China Time - Beijing',
        'Asia/Singapore' => 'Singapore',
        'Asia/Taipei' => 'Taipei',
        'Asia/Ulaanbaatar' => 'Ulaanbaatar',
        'Australia/Perth' => 'Western Time - Perth',
        'Asia/Pyongyang' => 'Pyongyang',
        'Asia/Dili' => 'Dili',
        'Asia/Jayapura' => 'Jayapura',
        'Asia/Seoul' => 'Seoul',
        'Asia/Tokyo' => 'Tokyo',
        'Asia/Yakutsk' => 'Moscow+06 - Yakutsk',
        'Pacific/Palau' => 'Palau',
        'Australia/Adelaide' => 'Central Time - Adelaide',
        'Australia/Darwin' => 'Central Time - Darwin',
        'Antarctica/DumontDUrville' => 'Dumont D\'Urville',
        'Asia/Vladivostok' => 'Moscow+07 - Vladivostok',
        'Australia/Brisbane' => 'Eastern Time - Brisbane',
        'Australia/Hobart' => 'Eastern Time - Hobart',
        'Australia/Sydney' => 'Eastern Time - Melbourne, Sydney',
        'Pacific/Chuuk' => 'Truk',
        'Pacific/Guam' => 'Guam',
        'Pacific/Port_Moresby' => 'Port Moresby',
        'Antarctica/Casey' => 'Casey',
        'Asia/Magadan' => 'Moscow+08 - Magadan',
        'Pacific/Efate' => 'Efate',
        'Pacific/Guadalcanal' => 'Guadalcanal',
        'Pacific/Kosrae' => 'Kosrae',
        'Pacific/Norfolk' => 'Norfolk',
        'Pacific/Noumea' => 'Noumea',
        'Pacific/Pohnpei' => 'Ponape',
        'Asia/Kamchatka' => 'Moscow+09 - Petropavlovsk-Kamchatskiy',
        'Pacific/Auckland' => 'Auckland',
        'Pacific/Fiji' => 'Fiji',
        'Pacific/Funafuti' => 'Funafuti',
        'Pacific/Kwajalein' => 'Kwajalein',
        'Pacific/Majuro' => 'Majuro',
        'Pacific/Nauru' => 'Nauru',
        'Pacific/Tarawa' => 'Tarawa',
        'Pacific/Wake' => 'Wake',
        'Pacific/Wallis' => 'Wallis',
        'Pacific/Apia' => 'Apia',
        'Pacific/Enderbury' => 'Enderbury',
        'Pacific/Fakaofo' => 'Fakaofo',
        'Pacific/Tongatapu' => 'Tongatapu',
        'Pacific/Kiritimati' => 'Kiritimat'
    ];

    /**
	 * Takes a date string and turns it into a datetime object converted to the timezone specified. 
	 * Note that fromTimezone is probably ignored if your date string has +8 or something at the end for the utc offset.
	 * Available timezones can be found at http://php.net/manual/en/timezones.php
	 * 
	 * @param string $dateString 
	 * @param string $initialTimeZone The timezone it is in now
	 * @param string $toTimeZone The timezone to convert to
	 * 
	 * @return \DateTime
	 */
    public static function convertStringToTimezone($dateString, $initialTimeZone, $toTimeZone) {
    	$dateTime = new \DateTime($dateString, new \DateTimeZone($initialTimeZone));
    	$dateTime->setTimezone(new \DateTimeZone($toTimeZone));
    	return $dateTime;
    }
    
    /**
     * Alias for convertStringToTimezone when you just want to switch a date's timezone to GMT
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    public static function convertToGMT($dateTime) {
    	return $dateTime->setTimezone(new \DateTimeZone('Etc/GMT+0'));
    }
    
    /**
     * Formats a date like 'Y-m-d\TH:i:s-07:00'. Expects a unix timestamp like time()
     * @param int $unixTimestamp
     * @return string
     */
    public static function formatDateForMySQL($unixTimestamp) {
    	return date(self::FORMAT_ISO8601, $unixTimestamp);
    }
    
    /**
     * Return a datetime object for now in the UTC timezone
     * @return \DateTime
     */
    public static function getCurrentDateTimeUTC() {
    	return new \DateTime('now', new \DateTimeZone(self::TZ_UTC));
    }
    
    /**
     * Returns the end date for the time interval starting at the start date. 
     * Note that it is always one second less than the next time range. So the end date for a day would be the time 23:59:59
     * @param \DateTime $startDate
     * @param string $timeRangeName Required A string representing the type of time interval to figure out such as (hour, day, week, month, year)
     * @return \DateTime
     */
    public static function getEndDateForTimeRange($startDate, $timeRangeName) {
    	$allowedTimeRanges = array('hour', 'day', 'week', 'month', 'year');
    	$timeRangeName = strtolower($timeRangeName);
    	if (array_search($timeRangeName, $allowedTimeRanges) === false) throw new \RuntimeException("The time range, $timeRangeName, is not supported");
    	return $startDate->modify('+1 '.$timeRangeName.'s')->modify('-1 seconds');
    }
    
    /**
     * 
     * @param \DateTime $startDate
     * @param int $durationSeconds Required The number of minutes long the time range should be. This just gets added to start date to make the end date
     * @return \DateTime
     */
    public static function getEndDateForDuration($startDate, $durationSeconds) {
    	return $startDate->modify('+'.$durationSeconds.' seconds');
    }

    /**
     * Returns the normal name for a timezone so America/Los_Angeles becomes 'Pacific Time'.
     * For just the abbreviation you can use ->format('T');
     * Returns an empty string '' if not found
     * @param string $timezone
     * @return string
     */
	public static function getNameForTimeZone($timezone) {
	    return (in_array($timezone, array_keys(static::TZ_TO_NAME_MAP))) ? static::TZ_TO_NAME_MAP[$timezone] : '';
	}

    /**
     * Return the number of seconds between two dates. D1 - D2.
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param boolean $absolute Optional Whether to take the absolute value so the answer is always positive. Default True
     * @return int|number
     */
	public static function getSecondsBetweenTwoDates($startDate, $endDate, $absolute = true) {
		$seconds = $startDate->getTimestamp() - $endDate->getTimestamp();
		return ($absolute) ? abs($seconds) : $seconds;
	}

    /**
     * Returns true if the date is in the past. Returns false if it is exactly now or in the future.
     * @param \DateTime $dateTime
     * @return bool
     */
    public static function isDateInPast($dateTime) {
        $now = new \DateTime('now', new \DateTimeZone(self::TZ_UTC));
        return $dateTime < $now;
    }

    /**
     * Returns true if the date is in the future. Returns false if it is exactly now or in the past.
     * @param \DateTime $dateTime
     * @return bool
     */
    public static function isDateInFuture($dateTime) {
        $now = new \DateTime('now', new \DateTimeZone(self::TZ_UTC));
        return $dateTime > $now;
    }

}