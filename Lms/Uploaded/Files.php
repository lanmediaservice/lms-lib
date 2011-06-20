<?php
/**
 * LMS Library 
 * 
 * @version $Id: Files.php 624 2011-02-13 19:56:19Z macondos $
 * @copyright 2009
 * @author Ilya Spesivtsev <macondos@gmail.com>
 */


class Lms_Uploaded_Files {
    private $_uploadedFiles;
    function __construct($config)
    {
        $this->_uploadedFiles = new Lms_Enumerator();
        foreach ($_FILES as $field=>$file) {
            $uploadedFile = new Lms_Uploaded_File(
                $file['name'],
                $file['type'],
                $file['size'],
                $file['tmp_name'],
                $file['error']
            );
            $uploadedFile->setConfig($config);
            $this->_uploadedFiles->add($uploadedFile);
        }
        if (isset($_POST['protect_code']) && $_POST['protect_code']==$config['protect_code']) {
            //nginx upload
            $config['move_method'] = 'rename';
            $files = array();
            foreach ($_POST as $k => $v) {
                if (preg_match('{^file(.*?)_(name|content_type|path|md5|sha1|crc32|size)$}i', $k, $matches)) {
                    $filenum = $matches[1];
                    $field = $matches[2];
                    if (in_array($field, array('sha1', 'md5', 'crc32'))) {
                        $files[$filenum]['hashes'][$field] = $v;
                    } else {
                        $files[$filenum][$field] = $v;
                    }
                    
                }
            }
            foreach ($files as $file) {
                
                $uploadedFile = new Lms_Uploaded_File(
                    isset($_POST['urlencoded'])? rawurldecode($file['name']) : $file['name'],
                    $file['content_type'],
                    $file['size'],
                    $file['path'],
                    0,
                    (isset($file['hashes'])? $file['hashes'] : array())
                );
                $uploadedFile->setConfig($config);
                $this->_uploadedFiles->add($uploadedFile);
            }
        }
    }
    
    public function __call($method, $val)
    {    
        return call_user_func_array(array($this->_uploadedFiles, $method), $val);
    }
}
 
?>