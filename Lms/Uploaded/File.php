<?php
/**
 * LMS Library 
 * 
 * @version $Id: File.php 350 2010-02-14 19:28:36Z macondos $
 * @copyright 2009
 * @author Ilya Spesivtsev <macondos@gmail.com>
 */

class Lms_Uploaded_File {
    var $_config;
    var $name;
    var $type;
    var $size;
    var $tempName;
    var $errorNumber;
    var $hashes;
    
    public function __construct($name, $type, $size, $tempName, $errorNumber, $hashes = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->tempName = $tempName;
        $this->errorNumber = $errorNumber;
        $this->hashes = $hashes;
    }

    public function setConfig($config)
    {
        $this->_config = $config;
    }
    
    public function getError()
    {
        return $this->errorNumber;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getTempName()
    {
        return $this->tempName;
    }
    
    public function hasErrors()
    {
        return $this->errorNumber!=0;
    }
    
    public function getHash($type)
    {
        return isset($this->hashes[$type])? $this->hashes[$type] : null;
    }
    public function moveTo($newPath) {
        if (isset($this->_config['move_method'])) {
            return call_user_func($this->_config['move_method'], $this->tempName, $newPath);
        } else {
            return move_uploaded_file($this->tempName, $newPath);
        }
    }
}

?>