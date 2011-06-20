<?php
/**
 * Класс для авторизации пользователей
 * 
 *
 * @copyright 2006-2010 LanMediaService, Ltd.
 * @license    http://www.lms.by/license/1_0.txt
 * @author Ilya Spesivtsev
 * @version $Id: Auth.php 290 2009-12-28 12:54:32Z macondos $
 * @category Lms
 * @package Lms_Auth
 */

/**
 * @category Lms
 * @package Lms_Auth
 */
 
class Lms_Auth
{
    const BY_LOGIN = 1;
    const BY_EMAIL = 2;
    const RESTORE_OK = 3;
    const RESTORE_FAILURE = 4;
    
    protected static $_activateByEmail=true;
    protected static $_loginType = self::BY_EMAIL;
    protected static $_useDisplayName = false;
    protected static $_db;
    protected static $_allowedCharacters;
    protected static $_numberOfAttempts = 2;
    
    static function readConfig($config)
    {
        $validKeys = array(
            'activate_by_email'=>'_activateByEmail', 
            'login_type'=>'_loginType', 
            'use_display_name'=>'_useDisplayName', 
            'allowed_characters'=>'_allowedCharacters',
            'number_of_attempts' => '_numberOfAttempts'
        );
        foreach ($config as $key=>$value) {
            if (in_array($key, array_keys($validKeys))) {
                $property = $validKeys[$key];
                self::$$property = $value;
            }
        }
    }
    
    public static function setActivateByEmail($value)
    {
        self::$_activateByEmail = $value;
        return $this;
    }
    
    public static function setLoginType($value)
    {
        switch ($value) {
            case self::BY_EMAIL :
            case self::BY_LOGIN :    
                self::$_loginType = $value;
                break;
            default:
                self::$_loginType = self::BY_EMAIL ;
                break;
        }
        return $this;
    }
    
    public static function setUseDisplayName($value)
    {
        self::$_useDisplayName = $value;
        return $this;
    }
    
    public static function setDb($db)
    {
        self::$_db = $db;
    }
    
    public function getActivateByEmail()
    {
        return self::$_activateByEmail;
    }
    
    public function getUseDisplayName()
    {
        return self::$_useDisplayName;
    }
    
    public function getLoginType()
    {
        return self::$_loginType;
    }
    
    public function getDb()
    {
        return self::$_db;
    }
    
    public function setAllowedCharacters($regexp)
    {
        self::$_allowedCharacters = $regexp;
    }
    
    public function getAllowedCharacters()
    {
        self::$_allowedCharacters;
    }
    
    protected  static function _generatePassword()
    {
        return substr(md5(rand(0, 1000000000)), 0, 8);
    }
    
    public function restoreAccount($email)
    {
        $db = self::$_db;
        $login = $db->selectCell(
            'SELECT `user_name` FROM ?_users_keys WHERE `email`=?',
            $email
        );
        if ($login) {
            $newPassword = self::_generatePassword();
            $db->query(
                'UPDATE ?_users_keys SET password_hash=?',
                md5($newPassword)
            );
            if (self::getLoginType() === self::BY_EMAIL ) {
                $login = $email;
            }
            return array('status' => self::RESTORE_OK, 
                         'password'=>$newPassword,
                         'login'=>$login);
        } 
        return array('status' => self::RESTORE_FAILURE);
    }
    
    public function getNumberOfAttempts()
    {
        return self::$_numberOfAttempts;
    }
}