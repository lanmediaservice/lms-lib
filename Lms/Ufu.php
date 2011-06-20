<?php
class Lms_Ufu {
    
    static public function nameToUrl($name)
    {
        return str_replace(' ', '_', $name);
    }
    
    static public function urlToName($url)
    {
        return str_replace('_', ' ', $url);
    }
}