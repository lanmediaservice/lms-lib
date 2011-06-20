<?php
/**
 * LMS Library
 *
 * 
 * @version $Id: Timers.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package Lms
 */

class Lms_Timers {
    var $timers;
    function __construct(){
        $this->timers = array();
    }

    function start($name){
        $timer = $this->_getTimer($name);
        return $timer->start();
    }    

    function stop($name){
        $timer = $this->_getTimer($name);
        return $timer->stop();
    }    

    function cancel($name){
        $timer = $this->_getTimer($name);
        return $timer->cancel();
    }    

    function reset($name){
        $timer = $this->_getTimer($name);
        return $timer->reset();
    }    

    function getSumTime($name){
        $timer = $this->_getTimer($name);
        return $timer->getSumTime();
    }    
    
    function getCount($name){
        $timer = $this->_getTimer($name);
        return $timer->getCount();
    }    
    
    function getTimersNames(){
        return array_keys($this->timers);
    }    

    function &_getTimer($name){
        if (!isset($this->timers[$name])) $this->timers[$name] = new Lms_Timer();
        return $this->timers[$name];
    }    
    
}

?>