<?php

class Lms_ZendCompiler {
    private $zendOutputDir;
    private $zendSourceDir;

    public function setOutputDir($zendOutputDir)
    {
        $this->zendOutputDir = $zendOutputDir;
    }
    
    public function setSourceDir($zendSourceDir)
    {
        $this->zendSourceDir = $zendSourceDir;
    }
    
    private function clean()
    {
        if (!$this->zendOutputDir) {
            throw new Exception('Undefined output dir');
        }
        echo "\nClean output dir";
        $folder = Lms_FileSystem::getFolder($this->zendOutputDir);
        $subFolders = $folder->getFolders();
        while (Lms_Enumerator::FAIL !== $subFolder = $subFolders->getEach()) {
            $subFolder->delete(true);
        } 
        $subFiles = $folder->getFiles();
        while (Lms_Enumerator::FAIL !== $subFile = $subFiles->getEach()) {
            $subFile->delete(true);
        } 
    }
    private function processFile($relativePath)
    {
        echo "\n    Process file: $relativePath ";
        $file = implode('', file($this->zendSourceDir . '/' . $relativePath));
        $file = preg_replace('/((?:require|include)_once\s*\(?[\'"]Zend\/(.*)[\'"]\)?\s*;)/smiU', '//*** $1', $file);
        file_put_contents($this->zendOutputDir . '/' . $relativePath, $file);
        echo "...OK";
    }
    private function processDir($relativePath = '')
    {
        if (!$this->zendSourceDir) {
            throw new Exception('Undefined source dir');
        }
        echo "\nProcess dir: $relativePath ";
        $zendSourceFolder = Lms_FileSystem::getFolder($this->zendSourceDir . '/' . $relativePath);
        Lms_FileSystem::createFolder($this->zendOutputDir . '/' . $relativePath);
        $subFolders = $zendSourceFolder->getFolders();
        while (Lms_Enumerator::FAIL !== $subFolder = $subFolders->getEach()) {
            $folderPath = $relativePath . '/' . $subFolder->getName();
            $this->processDir($folderPath);
        } 
        $files = $zendSourceFolder->getFiles();
        while (Lms_Enumerator::FAIL !== $file = $files->getEach()) {
            $filePath = $relativePath . '/' . $file->getName();
            $this->processFile($filePath);
        } 
        echo "\n/Process dir: $relativePath ";
    }
    
    public function run()
    {
        $this->clean();
        $this->processDir();
        
    }
}

?>