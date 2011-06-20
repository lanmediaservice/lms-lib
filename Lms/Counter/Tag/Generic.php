<?php
/**
 * lib
 * 
 * @version $Id: Generic.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package package_name
 */

abstract class Lms_Counter_Tag_Generic
{
    private $_value;
    
    public function __construct($value = null)
    {
        $this->setValue($value);
    }
    
    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }
}