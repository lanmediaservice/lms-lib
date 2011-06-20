<?php
/**
 * lib
 * 
 * @version $Id: Counter.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package package_name
 */

class Lms_Counter
{
    
    private $_cacher;
    private $_triggers;
        
    public function __construct($cacher)
    {
        $this->_cacher = $cacher;
    } 
    
    public function increment()
    {
        $tags = func_get_args();
        $tagsHash = $this->_tagsToTagsHash($tags);
        $value = $this->_loadFromCache($tagsHash, 0);
        $value++;
        $this->_saveToCache($tagsHash, $value);
        $this->_onChange('up', $tags, $value);
    }

    public function decrement()
    {
        $tags = func_get_args();
        $tagsHash = $this->_tagsToTagsHash($tags);
        $value = $this->_loadFromCache($tagsHash, 0);
        $value--;
        $this->_saveToCache($tagsHash, $value);
        $this->_onChange('down', $tags, $value);
    }

    public function setTrigger(
        $direction, $tagsNames, $value, $callback, $args = array()
    )
    {
        $value = (string) $value;
        $tagsNamesHash = $this->_tagsNamesToTagsNamesHash($tagsNames);
        $this->_triggers[$tagsNamesHash][$direction][$value] = array(
            'callback'=>$callback,
            'args' => $args
        );
    }

    public function getCount()
    {
        $tags = func_get_args();
        $tagsHash = $this->_tagsToTagsHash($tags);
        return $this->_loadFromCache($tagsHash, 0);
    }

    public function _onChange($direction, $tags, $value)
    {
        $value = (string) $value; 
        $tagsNamesHash = $this->_tagsToTagsNamesHash($tags);
        if (isset($this->_triggers[$tagsNamesHash][$direction][$value])) {
            $trigger = $this->_triggers[$tagsNamesHash][$direction][$value];
            call_user_func_array($trigger['callback'], $trigger['args']);
        }
    }
    
    private function _tagsToTagsHash($tags)
    {
        $tagsNames = array();
        foreach ($tags as $tag) {
            $tagsNames[] = get_class($tag);
        }
        array_multisort($tags, $tagsNames);
        $tagsHash = md5(serialize($tags));
        return $tagsHash;
    }

    private function _tagsNamesToTagsNamesHash($tagsNames)
    {
        sort($tagsNames);
        return md5(serialize($tagsNames));
    }

    private function _tagsToTagsNamesHash($tags)
    {
        $tagsNames = array();
        foreach ($tags as $tag) {
            $tagsNames[] = get_class($tag);
        }
        return $this->_tagsNamesToTagsNamesHash($tagsNames);
    }

    private function _loadFromCache($id, $defaultValue = false)
    {
        $data = $this->_cacher->load($id);
        if ($data === false ) {
            return $defaultValue;
        } else {
            return $data;
        }
    }

    private function _saveToCache($id, $value)
    {
        $this->_cacher->save($value, $id);
    }

}