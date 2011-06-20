<?php
/**
 * LMS Library
 *
 * @version $Id: String.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007-2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @author Alex Tatulchenkov<webtota@gmail.com>
 * @package Validator
 */

/**
 * Collection of filters for Enumerator when Enumerator's items are string 
 *
 */
class Lms_Validator_String {
	
    /**
     * Finds whether a length of $value is great than $number 
     *
     * @param mixed $value
     * @param int $number
     * @return bool
     */
    public static function isLongerThan($value, $number)
    {
        $value = (string)$value;
        return strlen($value) > $number;
    }
    /**
     * Finds whether a length of $value is less than $number 
     *
     * @param mixed $value
     * @param int $number
     * @return bool
     */
    public static function isShorterThan($value, $number)
    {
        $value = (string)$value;
        return strlen($value) < $number;
    }
    
    /**
     * Finds whether a string $value is match to pattern $pattern
     *
     * @param mixed $value
     * @param string $pattern
     * @return bool
     */
    public static function isMatch($value, $pattern)
    {
        return preg_match($pattern, $value);
    }
}
?>