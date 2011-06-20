<?php
/**
 * LMS2
 *
 * @version $Id: Mplayer.php 413 2010-04-17 22:13:29Z macondos $
 * @copyright 2008
 */

/**
 * Mplayer
 *
 * Class for analyze mediafile, generating screenshots and other, on *nix-OS
 * mplayer can launch in gnu screen utility for generating
 *
 * @author Alex Tatulchenkov
 * @copyright Copyright (c) 2008
 * @version $Id: Mplayer.php 413 2010-04-17 22:13:29Z macondos $
 * @access public
 */
class Lms_ExternalBin_Mplayer extends  Lms_ExternalBin_Generic
{


    private $_locationMplayer; // path for launching mplayer

    function __construct()
    {

    }

    /**
     * set path for launching mplayer
     *
     * @param unknown_type $path
     */
    public function setLocation($path)
    {
       $this->_locationMplayer = $path;
    }


    /**
     * Mplayer::analyze()
     *
     * @return
     */
    function analyze($pathToFile, $audioTrackId = false)
    {
        $commandStr = escapeshellcmd($this->_locationMplayer);
        $commandStr .=  ' -frames 0 -vo null -ao null -msglevel identify=6 ';
        if ($audioTrackId!==false) {
            $commandStr .= " -aid $audioTrackId ";
        }
        $commandStr .= escapeshellarg($pathToFile);
        $lines = array();
        exec($commandStr, $lines);

        $info = array();
        $numberOfAudioTracks = 0;
        foreach ($lines as $line) {
            //echo $line;
            if (preg_match("/^(ID_.*?)=(.*?)$/", $line, $matches)) {
                if ($matches[1] == 'ID_AUDIO_ID') $numberOfAudioTracks++;
                $info[$matches[1]] = $matches[2];
            }
        }
        if (count($info) < 1) {
            throw new Lms_ExternalBin_Exception(
                "Cannot parse file: $pathToFile"
            );
        }
        $info['numberOfAudioTracks'] = $numberOfAudioTracks;
        return $info;
    }
    

    /**
     * Mplayer::generate_screenshots()
     *
     * @return
     */
    function generate_screenshots()
    {
    }

    /**
     * Mplayer::convert()
     *
     * @return
     */
    function convert()
    {
    }

    function start($str = false)
    {
        if (!$str) {
            $str = $this->_locationMplayer;
        }
        return ExternalProgram::start($str);
    }

}
