<?php
/**
 * lib
 * 
 * @version $Id: Streamer.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package package_name
 */

class Lms_Http_Streamer
{
    private $_packetSizeInBytes = 8192;
    private $_speedInBytesPerSecond;
    private $_fileName;
    private $_path;
    private $_offset = 0;

    private $_onDownloadCallback;
    private $_onDownloadNextCallTime;
    private $_onDownloadTimeout;
    
    private $_onSpeedCallback;
    private $_onSpeedNextCallTime;
    private $_onSpeedTimeout;

    private $_quantTimeInSeconds;
    private $_quantTime;
    private $_sleepFunc;

    private $_mimeType = 'application/octet-stream';
    private $_charset;

    private $_sendContentDisposition = true;

    private $_isWindows = false;    
    private $_minBlockSize = 131072;
    private $_maxBlockSize = 1048576;

    private $_obFlushEnabled = false;
    private $_flushEnabled = false;
    
    function __construct()
    {
        $this->_isWindows = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
    }
        
    public function setMinBlockSize($minBlockSize)
    {
        $this->_minBlockSize = $minBlockSize;
    }
    
    public function setMaxBlockSize($maxBlockSize)
    {
        $this->_maxBlockSize = $maxBlockSize;
    }
    
    public function enableObFlush()
    {
        $this->_obFlushEnabled = true;
    }

    public function enableFlush()
    {
        $this->_flushEnabled = true;
    }
    
    public function setContentDisposition($on)
    {
        $this->_sendContentDisposition = (bool) $on;
    }

    public function setPath($path)
    {
        $this->_path = $path;
        if (!$this->_fileName) $this->_fileName = basename($path);
    }

    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;
    }

    public function setMimeType($mimeType)
    {
        $this->_mimeType = $mimeType;
    }

    public function setDefaultSpeed($speedInBytesPerSecond = 16384)
    {
        $this->_speedInBytesPerSecond = $speedInBytesPerSecond;
    }

    public function setPacketSize($sizeInBytes)
    {
        $this->_packetSizeInBytes = $sizeInBytes;
    }
    
    public function setOnSpeedCallback($callback, $timeoutInSeconds = 60)
    {
        if (is_callable($callback) && ($timeoutInSeconds>0)) {
            $this->_onSpeedCallback = $callback;
            $this->_onSpeedTimeout = $timeoutInSeconds;
        }
    }

    public function setOnDownloadCallback($callback, $timeoutInSeconds = 0)
    {
        if (is_callable($callback)) {
            $this->_onDownloadCallback = $callback;
            $this->_onDownloadTimeout = $timeoutInSeconds;
        } else {
            throw new Lms_Exception('Invalid onDownload callback');
        }
    }
    
    public function send()
    {
        @ob_end_clean();
        if (!$this->_prepareHeader()) {
            return false;
        }
        
        if ('HEAD' == $_SERVER['REQUEST_METHOD']) {
            return true;
        }

        $handle = fopen($this->_path, 'rb');
  
        if ($handle === false) {
            return false;
        }

        if ($this->_offset) fseek($handle, $this->_offset);
  
        ignore_user_abort(true);
        
        $this->_onSpeedNextCallTime = time() + $this->_onSpeedTimeout;
        if ($this->_onDownloadTimeout) {
            $this->_onDownloadNextCallTime = time() + $this->_onDownloadTimeout;
        }
        
        $packetCounter = 0;
        $compensation = 0;
        
        $downloadBytesCounter = 0;
        $sendBytesCount = 0;
        $blockSize = 0;
        $offset = $this->_offset;
        while ((connection_status()==0) && !feof($handle)) {
            $currentMicrotime = $this->_getmicrotime();
            if ($this->_speedInBytesPerSecond) {
                $blockSize = $this->_speedInBytesPerSecond;
                if ($blockSize < $this->_minBlockSize) {
                    $blockSize = $this->_minBlockSize;
                }
                if ($blockSize > $this->_maxBlockSize) {
                    $blockSize = $this->_maxBlockSize;
                }
            } else {
                $blockSize = 131072;
            }

            $sendBytesCount = $this->_readAndFlushFile($handle, $blockSize);
            //Lms_Debugger::log("SEND: $sendBytesCount; OFFSET: $offset");
            //$offset += $sendBytesCount;

            $downloadBytesCounter += $sendBytesCount;
            if ($this->_onSpeedCallback
                && ($currentMicrotime>=$this->_onSpeedNextCallTime)
            ) {
                $this->_onSpeedNextCallTime = 
                    $currentMicrotime + $this->_onSpeedTimeout;

                $this->_speedInBytesPerSecond = call_user_func(
                    $this->_onSpeedCallback
                );
            }

            if ($this->_onDownloadCallback 
                && $this->_onDownloadTimeout
                && ($currentMicrotime>=$this->_onDownloadNextCallTime)
            ) {
                $this->_onDownloadNextCallTime =
                    $currentMicrotime + $this->_onDownloadTimeout;

                $resume = call_user_func(
                    $this->_onDownloadCallback,
                    $downloadBytesCounter, $this
                );
                $downloadBytesCounter = 0;
                if (!$resume) {
                    break;
                };
            }
            if ($this->_speedInBytesPerSecond) {
                $restSend = $this->_speedInBytesPerSecond - $sendBytesCount;
                $sendedPart = $restSend / $this->_speedInBytesPerSecond;
                $elapsedTime = ($this->_getmicrotime()-$currentMicrotime);
                $delayInSeconds = 1 - $sendedPart - $elapsedTime;
                $this->_sleep($delayInSeconds);
            }
        };

        if ($this->_onDownloadCallback) {
            call_user_func(
                $this->_onDownloadCallback,
                $downloadBytesCounter, $this
            );
        }
        
        fclose($handle);
        return true;
    }

    private function _readAndFlushFile($handle, $minLength) 
    { 
        $realSendBytesCount = 0;
        while ((connection_status()==0) 
               && ($realSendBytesCount<$minLength) && !feof($handle)
        ) {
            $buffer = fread($handle, $this->_packetSizeInBytes);
            $realSendBytesCount += strlen($buffer);
            echo $buffer;
            if ($this->_obFlushEnabled) {
                @ob_flush();
            }
            if ($this->_flushEnabled) {
                @flush();
            }
        }
        return $realSendBytesCount;
    } 

    private function _getmicrotime() 
    { 
        list($usec, $sec) = explode(" ", microtime()); 
        return ((float)$usec + (float)$sec); 
    } 

    private function _sleep($delayInSeconds)
    {
        if ($delayInSeconds<=0) return;
        if ($this->_isWindows) {
            sleep((int)max(1, $delayInSeconds));
        } else {
            usleep($delayInSeconds * 1E+6);
        }
    }
    
    private function _prepareHeader()
    {
        $fileSize = filesize($this->_path);
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->_offset = $_SERVER['HTTP_RANGE'];
            $this->_offset = str_replace("bytes=", "", $this->_offset);
            $this->_offset = str_replace("-", "", $this->_offset);
        } else {
            $this->_offset = 0;
        }
        if ($this->_offset>$fileSize) {
            $this->_offset = 0;
        }
        
        if (!file_exists($this->_path)) {
            $this->_setHeaderStatus(404);
            return false;
        } elseif (!is_readable($this->_path)) {
            $this->_setHeaderStatus(403);
            echo $this->_path;
            return false;
        } else {
            if ($this->_offset) {
                $this->_setHeaderStatus(206);
                header('Accept-Ranges: bytes');
                $contentRange = sprintf(
                    'bytes %d-%d/%d',
                    $this->_offset, $fileSize, $fileSize
                );
                header("Content-Range: " . $contentRange);
            } else {
                $this->_setHeaderStatus(200);
            }
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            $contentType = $this->_mimeType;
            if ($this->_charset) {
                $contentType .= '; charset="' . $this->_charset . '"';
            }
            header('Content-Type: ' . $contentType, true);
            //header('Content-Type: ' . $this->_mimeType, true);
            if ($this->_sendContentDisposition) {
                $contentDisposition = 'attachment;'
                                    . ' filename="' . $this->_fileName . '"';
                header('Content-Disposition: ' . $contentDisposition);
            }
            header('Content-Length: '.($fileSize - $this->_offset));
             header("Content-Transfer-Encoding: binary");
            @ini_set('max_execution_time', 0);
            @set_time_limit();
            return true;
        }
    }
    
    private function _setHeaderStatus($statusCode)
    {
        static $messages = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
    
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
    
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
    
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
    
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        $message = $messages[$statusCode];
        header(
            $_SERVER['SERVER_PROTOCOL'] . " $statusCode $message",
            true, $statusCode
        );
        header("Status: $statusCode $message", true, $statusCode);
    }
}