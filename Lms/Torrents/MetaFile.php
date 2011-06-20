<?php
/**
 * LMS Library
 *
 * 
 * @version $Id: MetaFile.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007-2009
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package Torrents
 */

/**
 * @package Torrents
 */ 
class Lms_Torrents_MetaFile {
    var $source;
    var $torrentArray;
    var $_isLoaded;
    var $_encoding;
    var $_files;
    var $_dirSeparator;
    
    public function __construct($dirSeparator = "/") {
        $this->_isLoaded = false;
        $this->_dirSeparator = $dirSeparator;
    }

    public function loadByContent($content) {
        $this->source = $content;
        return $this->_parse();
    }


    public function loadByFileName($filename) {
        $this->source = file_get_contents($filename);
        return $this->_parse();
    }

    private function _isValidTorrentFile(){
        if (!isset($this->torrentArray['info'])) return false;
        if (!isset($this->torrentArray['info']['name'])) return false;
        if (!isset($this->torrentArray['info']['piece length'])) return false;
        if (!isset($this->torrentArray['info']['pieces'])) return false;
        if (!(isset($this->torrentArray['info']['length']) || isset($this->torrentArray['info']['files']))) return false;
        return true;
    } 

    private function _parse() {
        $this->torrentArray = Lms_BTransform::decode($this->source);
        $this->_isLoaded = $this->_isValidTorrentFile();
        $this->_files = array();
        if ($this->_isLoaded){
            $this->_encoding = isset($this->torrentArray['encoding'])? $this->torrentArray['encoding'] : 'UTF-8';
            if (isset($this->torrentArray['info']['files'])){
                $dirName = $this->torrentArray['info']['name'];
                foreach ($this->torrentArray['info']['files'] as $file){
                    $filePath = $dirName . $this->_dirSeparator . implode($this->_dirSeparator, $file['path']);
                    $this->_files[] = array('path' => $filePath,'size' => $file['length']);
                }
            } else{
                $filePath = $this->torrentArray['info']['name'];
                $fileSize = $this->torrentArray['info']['length'];
                $this->_files[] = array('path' => $filePath,'size' => $fileSize);
            }
        }
        return $this->_isLoaded;
    }
    
    public function getFiles(){
        return ($this->_isLoaded)? $this->_files : false;
    }
    
    public function getEncoding(){
        return $this->_encoding;
    }
}

?>