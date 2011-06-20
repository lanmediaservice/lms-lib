<?php
/**
 * LMS Library
 *
 * @version $Id: Object.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007-2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @author Alex Tatulchenkov<webtota@gmail.com>
 * @package Validator
 */

/**
 * Collection of filters for Enumerator when Enumerator's items are objects 
 *
 */
class Lms_Validator_Object {
	
    /**
     * Check $value is  an instance of class $className 
     *
     * @param mixed $value
     * @param string $className
     * @return bool
     */
	public static function isInstanceOf($value, $className)
	{
		return $value instanceof $className;
	}
	
	/**
	 * Check $value is a subclass of $className
	 *
	 * @param mixed $value
	 * @param string $className
	 * @return bool
	 */
	public static function isSubclassOf($value, $className)
	{
	    return is_subclass_of($value, $className);
	}
	
	/**
	 * check class with name $value is exists and try to autoload it
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function isLinkingClass($value)
	{
	    return class_exists($value, true);
	}
	
	/**
	 * Check wheter classname of $value is equal to $className
	 *
	 * @param mixed $value
	 * @param string $className
	 * @return bool
	 */
	public static function isClassName($value, $className)
	{
	    return (get_class($value) == $className)? true : false;
	} 

    /**
     * Check wheter classname of $value is equal to $className
     *
     * @param mixed $value
     * @param string $className
     * @return bool
     */
    public static function propertyClassIs($value, $className)
    {
        return ($value->getClass() == $className)? true : false;
    } 
}
?>