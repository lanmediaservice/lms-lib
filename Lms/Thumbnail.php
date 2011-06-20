<?php
class Lms_Thumbnail {
    
    private static $cacheDir;
    private static $errorImagePath;
    private static $imageDir;
    private static $httpClient;
    
    static function thumbnail($imgPath, &$width=0, &$height=0, $tolerance = 0.00, $zoom = true, $force = false) 
    {
        if (preg_match('{^https?://}i', $imgPath)) {
            $hash = md5($imgPath);
            $fileDirPath = self::$cacheDir . implode("/", str_split(substr($hash, 0, 2))) . "/" . $hash;
            //$path = self::$cacheDir . $hash;
            $path = null;
            if (is_dir($fileDirPath)) {
                $folder = Lms_FileSystem::getFolder($fileDirPath);
                $files = $folder->getFiles();
                if ($files->getCount()) {
                    $path = $files->getFirst()->getPath();
                }
            }
            if (!$path) {
                $tempPath = $fileDirPath . '/tmp';
                self::downloadImage($imgPath, $tempPath);
                $imageFormat = self::getFormat($tempPath);
                $path = "$fileDirPath/image.$imageFormat";
                Lms_Ufs::rename($tempPath, $path);
            }
        } else {
            $path = str_replace('\\', '/', $imgPath);
        }
        $imageFormat = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $imageSize = getimagesize($path);
        $imageX    = $imageSize[0];
        $imageY    = $imageSize[1];
        $imageType = $imageSize[2];
        $imageFormat = self::getFormat($path);

        $image = array();
        $image['x'] = $imageX;
        $image['y'] = $imageY;
        // check resize
        $k = $height ? ($height - $imageY) / $height : 0;
        if (!$zoom && $k>0) {
            $k = 0;
        }
        if ($height && (abs($k) > $tolerance)) {
            $image['y'] = $height;
            $image['x'] = round($image['x'] / (1 - $k));
            
        }
        $k = $width ? ($width - $image['x']) / $width : 0;
        if (!$zoom && $k>0) {
            $k = 0;
        }
        if ($width && (abs($k) > $tolerance)) {
            $image['x'] = $width;
            $image['y'] = round($image['y'] / (1 - $k));
        }
        $width = $image['x'];
        $height = $image['y'];
        $prefix = md5($imgPath);
        $prefix = implode("/", str_split(substr($prefix, 0, 2))) . "/" . $prefix;
        //$cachepath = LMS_PUBLIC_MEDIA_DIR . 'cache/';
        $cachepath = self::$cacheDir;
        if ((($image['x'] != $imageX) || ($image['y'] != $imageY) || $force) && function_exists('imagecreatefromjpeg')) {
            $filepath = $cachepath . $prefix . "_" . $image['x'] . "x" . $image['y'] . "." . $imageFormat;
            if (!is_file($filepath)) {
                $obj = new Lms_ImageProcessor();
                try {
                    $obj->loadfile($path);
                    $obj->resize($image['x'], $image['y']);
                    Lms_FileSystem::createFolder(dirname($filepath), 0777, true);
                    $obj->savefile($filepath);
                } catch (Exception $e) {
                    //$filepath = LMS_PUBLIC_COMMON_MEDIA_DIR . 'error.jpg';
                    $filepath = self::$errorImagePath;
                }
            }
            $url = $filepath;
        } else {
            $url = $path;
        }
        $url = str_replace('\\', '/', $url);
        $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $url);
        return $url;
    }
    
    static public function setCacheDir($dir)
    {
        self::$cacheDir = rtrim($dir, '/') . '/';
    }
    
    static public function setErrorImagePath($path)
    {
        self::$errorImagePath = $path;
    }
    
    static public function setImageDir($dir)
    {
        self::$imageDir = rtrim($dir, '/') . '/';
    }
    
    /**
     * Save file from remote machine to localhost
     *
     * @param string $strategy
     * @param array $params
     */
    static public function localize($path, $strategy, $params = array())
    {
        
        $oldPath = $path;
        $depth = 3;
        $imageDir = self::$imageDir;
        switch ($strategy) {
        case "cover":
            $imageDir .= 'covers/';
            $depth = 2;
            break;
        case "photo":
            $imageDir .= 'photos/';
            $depth = 3;
            break;
        case "screenshot":
            $imageDir .= 'screenshots/';
            $depth = 3;
            break;
        case "poster":
            $imageDir .= 'posters/';
            $depth = 2;
            break;
        case "cache":
            $imageDir .= 'cache/';
            $depth = 2;
            break;
        }
        if (isset($params['extension'])) {
            $extension = $params['extension'];
        } else {
            $extension = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        }
        $basename = (isset($params['name']) && $params['name'])? $params['name'] : basename($oldPath);
        $basename = Lms_LangHelpers::translit($basename);
        $basename = strtolower($basename);
        $basename = Lms_Ufu::nameToUrl($basename);
        $basename = ereg_replace('[^a-z0-9!@_\.-]', '', $basename);
        
        if (strlen(trim($basename))) {
            do {
                $newPath = $imageDir . self::_generateRandomPrefixFolders($depth) . $basename . '.' . $extension;
                $depth++; 
            } while (Lms_Ufs::file_exists($newPath));
            
            Lms_FileSystem::createFolder(dirname($newPath), 0777, true);
            if (preg_match('{^https?://}i', $oldPath)) {
                self::downloadImage($oldPath, $newPath);
            } else {
                Lms_Ufs::copy($oldPath, $newPath);
            }
            return $newPath;
        }
        return $oldPath;
    }
    
    /**
     * generate random tree of folders with setted depth
     *
     * @param int $depth
     * @param string $separator
     * @return string
     */
    static private function _generateRandomPrefixFolders($depth=3, $separator = '/')
    {
        $folders = array();
        for ($i=0; $i<$depth; $i++) {
            $folders[] = rand(0, 9);
        }
        $folders[] = '';
        return implode($separator, $folders);
    }

    static public function downloadImage($url, $saveTo)
    {
        if (!self::$httpClient) {
            self::$httpClient = new Zend_Http_Client();
        }
        $headers = array();
        $headers['Accept'] = '*/*';
        $headers['Accept-Language'] = 'ru';
        $headers['Accept-Encoding'] = 'gzip, deflate';
        $headers['Referer'] = dirname($url);
        $response = self::$httpClient->setUri($url)
                                     ->setHeaders($headers)
                                     ->request(Zend_Http_Client::GET);
        $imageContent = $response->getBody();
        Lms_FileSystem::createFolder(dirname($saveTo), 0777, true);
        file_put_contents($saveTo, $imageContent);
    }

    static public function getFormat($imagePath)
    {
        $imageSize = getimagesize($imagePath);
        $imageType = $imageSize[2];

        $types = array(
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
            4 => 'swf',
            5 => 'psd',
            6 => 'bmp',
            7 => 'tiff',
            8 => 'tiff',
            9 => 'jpc',
            10 => 'jp2',
            11 => 'jpx',
            12 => 'jb2',
            13 => 'swc',
            14 => 'iff',
            15 => 'wbmp',
            16 => 'xbm'
        );
        if (isset($types[$imageType])) {
            return $types[$imageType];
        } else {
            return 'jpg';
        }
    }
    
    static public function setHttpClient($httpClient)
    {
        self::$httpClient = $httpClient;
    }
}