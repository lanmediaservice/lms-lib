<?php
/** 
 * LMS Library
 * 
 * @package BTransform
 * @version $Id: BTransform.php 260 2009-11-29 14:11:11Z macondos $
 * @copyright 2007-2009
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * 
 */


/**
 * Encode/decode data by b-decoder algorithm
 * @package BTransform
 */
class Lms_BTransform
{
    /**
     * @access public
     * @static
     * @param int|float|array $var
     * @return string
     */
    public static function encode($var)
    {
        if (is_int($var)) {
            return 'i'. $var .'e';
        } elseif (is_float($var)) {
            return 'i'. sprintf('%.0f', $var) .'e';
        } elseif (is_array($var)) {
            if (count($var) == 0) {
                return 'de';
            } else {
                $assoc = false;
                foreach ($var as $key => $val) {
                    if (!is_int($key) && !is_float($var)) {
                        $assoc = true;
                        break;
                    }
                }
                if ($assoc) {
                    ksort($var, SORT_REGULAR);
                    $ret = 'd';
                    foreach ($var as $key => $val) {
                        $ret .= Lms_BTransform::encode($key)
                              . Lms_BTransform::encode($val);
                    }
                    return $ret .'e';
                } else {
                    $ret = 'l';
                    foreach ($var as $val) {
                        $ret .= Lms_BTransform::encode($val);
                    }
                    return $ret .'e';
                }
            }
        } else {
            return strlen($var) .':'. $var;
        }
    }    
    
    /**
     * @access public
     * @static
     * @param string $encodedStr
     * @return array
     */
    public static function decode($encodedStr)
    {
        $pos = 0;
        return Lms_BTransform::_bdecodeRecursive($encodedStr, $pos);
    }
    
    /**
     * @access private
     * @static
     */
    private static function _bdecodeRecursive($encodedStr, &$pos)
    {
        $encodedStrlen = strlen($encodedStr);
        if (($pos < 0) || ($pos >= $encodedStrlen)) {
            return NULL;
        } else if ($encodedStr{$pos} == 'i') {
            $pos++;
            $numlen = strspn($encodedStr, '-0123456789', $pos);
            $spos = $pos;
            $pos += $numlen;
            if (($pos >= $encodedStrlen) || ($encodedStr{$pos} != 'e')) {
                return NULL;
            } else {
                $pos++;
                return floatval(substr($encodedStr, $spos, $numlen));
            }
        } elseif ($encodedStr{$pos} == 'd') {
            $pos++;
            $ret = array();
            while ($pos < $encodedStrlen) {
                if ($encodedStr{$pos} == 'e') {
                    $pos++;
                    return $ret;
                } else {
                    $key = Lms_BTransform::_bdecodeRecursive($encodedStr, $pos);
                    if ($key === NULL) {
                        return NULL;
                    } else {
                        $val = Lms_BTransform::_bdecodeRecursive(
                            $encodedStr, $pos
                        );
                        if ($val === NULL) {
                                return NULL;
                        } elseif (!is_array($key)) {
                                $ret[$key] = $val;
                        }
                    }
                }
            }
            return NULL;
        } elseif ($encodedStr{$pos} == 'l') {
            $pos++;
            $ret = array();
            while ($pos < $encodedStrlen) {
                if ($encodedStr{$pos} == 'e') {
                        $pos++;
                        return $ret;
                } else {
                    $val = Lms_BTransform::_bdecodeRecursive($encodedStr, $pos);
                    if ($val === NULL) {
                        return NULL;
                    } else {
                        $ret[] = $val;
                    }
                }
            }
            return NULL;
        } else {
            $numlen = strspn($encodedStr, '0123456789', $pos);
            $spos = $pos;
            $pos += $numlen;
            if (($pos >= $encodedStrlen) || ($encodedStr{$pos} != ':')) {
                    return NULL;
            } else {
                $vallen = intval(substr($encodedStr, $spos, $numlen));
                $pos++;
                $val = substr($encodedStr, $pos, $vallen);
                if (strlen($val) != $vallen) {
                    return NULL;
                } else {
                    $pos += $vallen;
                    return $val;
                }
            }
        }
    }
    
}