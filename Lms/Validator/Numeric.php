<?php
/**
 * LMS Library
 *
 * @version $Id: Numeric.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007-2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @author Alex Tatulchenkov<webtota@gmail.com>
 * @package Validator
 */

/**
 * Collection of filters for Enumerator when Enumerator's items are numeric 
 *
 */
class Lms_Validator_Numeric {

    /**
     * Finds whether a variable is an odd number 
     *
     * @param mixed $value
     * @return bool
     */
    public static function isOdd($value)
    {
        return (bool)($value % 2);
    }

    /**
	 * Finds whether a variable is an even number 
	 *
	 * @param mixed $value
	 * @return bool
	 */
    public static function isEven($value)
    {
        return !(bool)($value % 2);
    }

    /**
	 * Finds whether a variable $value is great than $number
	 *
	 * @param mixed $value
	 * @param int $number
	 * @return bool
	 */
    public static function isGreat($value, $number)
    {
        return (intval($value) > intval($number));
    }

    /**
	 * Finds whether a variable $value is less than $number
	 *
	 * @param mixed $value
	 * @param int $number
	 * @return bool
	 */
    public static  function isLess($value, $number)
    {
        return (inval($value) < intval($number));
    }
}
?>