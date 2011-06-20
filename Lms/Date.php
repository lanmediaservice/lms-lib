<?php
/**
 * lib
 * 
 * @version $Id: Date.php 715 2011-06-20 10:47:24Z macondos $
 * @copyright 2008
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @package package_name
 */


class Lms_Date
{
    const DEFAULT_UNITS = 'ymdhis';
    const YEAR = 31536000;
    const MONTH = 2629800;
    const WEEK = 604800;
    const DAY = 86400;
    const HOUR = 3600;
    const MIN = 60;
        
    static public function timeAgo(
        $dateStr, $limitReturnChunks = 1,
        Zend_Translate $translator = null, $lang = 'en', 
        $units = self::DEFAULT_UNITS, $precision = 0.5
    )
    {
        $date = date('Y-m-d H:i:s');
        if ($date == $dateStr) {
            $outputStr = $translator? $translator->translate('now') : 'now';
        } else {
            $outputStr = self::timeDiff(
                $dateStr, $date, $limitReturnChunks,
                $translator, $lang, $units, $precision
            );
            if ($outputStr) {
                $outputStr .= ' ' . ($translator? $translator->translate('ago') : 'ago');
            } else {
                $outputStr = $translator? $translator->translate('now') : 'now';
            }
        }
        return trim($outputStr);
    }

    static public function timeAfter($dateOrigin, $dateCurrent, 
        $limitReturnChunks = 1, Zend_Translate $translator = null, $lang = 'en',
        $units = self::DEFAULT_UNITS, $precision = 0.5
    )
    {
        if ($dateOrigin == $dateCurrent) {
            if ($translator) {
                $outputStr = $translator->translate('at once');
            } else {
                $outputStr = 'at once';
            }
        } else {
            $outputStr = self::timeDiff(
                $dateOrigin, $dateCurrent, $limitReturnChunks,
                $translator, $lang, $units, $precision
            );
            if ($translator) {
                $outputStr = $translator->translate('after') . " $outputStr";
            } else {
                $outputStr = "after $outputStr";
            }
        }
        return trim($outputStr);
    }
    
    static private function timeDiff($dateFirst, $dateLast, 
        $limitReturnChunks = 1, Zend_Translate $translator = null, $lang = 'en',
        $units = self::DEFAULT_UNITS, $precision = 0.25
    )
    {
        $timeFirst = strtotime($dateFirst);
        $timeLast = strtotime($dateLast);
        
        $diff = abs($timeLast - $timeFirst);
        $rest = $diff;
        $restChunks = $limitReturnChunks;
        
        if (strpos($units, 'y')!==false) {
            $years = floor($rest/self::YEAR);
            if ($restChunks<=1 && (($rest - $years * self::YEAR)/$diff)>$precision) {
                $years = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $years * self::YEAR;
        } else {
            $years = 0;
        }
        
        if (strpos($units, 'm')!==false) {
            $months = floor($rest / self::MONTH);
            if ($restChunks<=1 && (($rest - $months * self::MONTH)/$diff)>$precision) {
                $months = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $months * self::MONTH;
        } else {
            $months = 0;
        }
        
        if (strpos($units, 'w')!==false) {
            $weeks = floor($rest / self::WEEK);
            if ($restChunks<=1 && (($rest - $weeks * self::WEEK)/$diff)>$precision) {
                $weeks = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $weeks * self::WEEK;
        } else {
            $weeks = 0;
        }

        if (strpos($units, 'd')!==false) {
            $days = floor($rest / self::DAY);
            if ($restChunks<=1 && (($rest - $days * self::DAY)/$diff)>$precision) {
                $days = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $days * self::DAY;
        } else {
            $days = 0;
        }
        
        if (strpos($units, 'h')!==false) {
            $hours = floor($rest / self::HOUR);
            if ($restChunks<=1 && (($rest - $hours * self::HOUR)/$diff)>$precision) {
                $hours = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $hours * self::HOUR;
        } else {
            $hours = 0;
        }
        
        if (strpos($units, 'i')!==false) {
            $mins = floor($rest / self::MIN);
            if ($restChunks<=1 && (($rest - $mins * self::MIN)/$diff)>$precision) {
                $mins = 0;
            } else {
                $restChunks--;
            }
            $rest = $rest - $mins * self::MIN;
        } else {
            $mins = 0;
        }
        
        if (strpos($units, 's')!==false) {
            $seconds = $rest;
        } else {
            $seconds = 0;
        }
                
        $chunks = array(
            'year(s)' => $years,
            'month(s)' => $months,
            'week(s)' => $weeks,
            'day(s)' => $days,
            'hour(s)' => $hours,
            'min(s)' => $mins,
            'second(s)' => $seconds,
        );
        
        $outputStr = '';
        
        $i = 0;
        foreach ($chunks as $chunkName => $value) {
            if ($value) {
                if ($translator) {
                    $chunkName = $translator->translate($chunkName);
                }
                $translatedChunkNames = explode(" ", $chunkName);
                $translatedChunkName = Lms_Text::declension(
                    $value, $translatedChunkNames, $lang
                );
                $outputStr .= " $value $translatedChunkName";
                $i++;
                if ($i>=$limitReturnChunks) {
                    break;
                }
            }
        }
        $outputStr = trim($outputStr);
        return $outputStr;
    }
    
    static public function timestamp($date, $timezone='UTC')
    {
        $datetime = new DateTime($date, new DateTimeZone($timezone));
        return $datetime->format('U');
    } 
    
}
