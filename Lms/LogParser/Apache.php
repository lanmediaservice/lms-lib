<?php 
/**
 * LMS Library
 * 
 * @version $Id: Apache.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2005-2009
 * @author Sam Clarke <admin@free-webmaster-help.com>
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package LogParser
 */

class Lms_LogParser_Apache
{
    private $_badRows; // Number of bad rows
    private $_fp; // File pointer

    private function formatLogLine($line)
    {
        $regExp = '{^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] '
                . '"(\S+) (.*?) (\S+)" (\S+) (\S+) (".*?") (".*?")$}';
        preg_match($regExp, $line, $matches); // pattern to format the line
        return $matches;
    }

    public function formatLine($line)
    {
        $logs = $this->formatLogLine($line); // format the line

        if (isset($logs[0])) {
            $formatedLog = array(); // make an array to store the lin info in
            $formatedLog['ip'] = $logs[1];
            $formatedLog['identity'] = $logs[2];
            $formatedLog['user'] = $logs[2];
            $formatedLog['date'] = $logs[4];
            $formatedLog['time'] = $logs[5];
            $formatedLog['timezone'] = $logs[6];
            $formatedLog['method'] = $logs[7];
            $formatedLog['path'] = $logs[8];
            $formatedLog['protocal'] = $logs[9];
            $formatedLog['status'] = $logs[10];
            $formatedLog['bytes'] = $logs[11];
            $formatedLog['referer'] = $logs[12];
            $formatedLog['agent'] = $logs[13];
            return $formatedLog; // return the array of info
        } else {
            $this->_badRows++;
            return false;
        }
    }

    public function openLogFile($fileName)
    {
        $this->_fp = fopen($fileName, 'r'); // open the file
        if (!$this->_fp) {
            return false; // return false on fail
        }
        return true; // return true on sucsess
    }

    public function closeLogFile()
    {
        return fclose($this->_fp); // close the file
    }

    // gets a line from the log file
    public function getLine()
    {
        if (feof($this->_fp)) {
            return false;
        }
        $bits = fgets($this->_fp, 1024);
        return rtrim($bits, "\n");
    }
}