<?php
/**
 * Класс для логарифмического разбивания на страницы
 * Пример ниже показывает, что алгоритм удобно использовать с привычным
 * использованием LIMIT
 * 
 * Usage:
 * 
 * $pageSize = 20;
 * $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
 * mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM mytable LIMIT $offset, $pageSize");
 * ....//'SELECT FOUND_ROWS()' -> $count
 * $paginator = new Lms_Paginator($count);
 * $pages = $paginator->setItemCountPerPage(20)
 *                    ->setOffset($offset)
 *                    ->getPages();
 *
 * foreach ($pages as $pageNumber => $pageOptions) {
 *    $name = $pageOptions['short']? '.' :  ' ' . self::simplifyNumber($pageNumber) . ' ';
 *    echo "<a title='$pageNumber' href='?offset={$pageOptions['new_offset']}'>$name</a>";
 * }
 */

 class Lms_Paginator {
    
    const SI_PREFIX = 1;
    const EXP_NOTATION = 2;
    
    private $_size;
    private $_page;
    private $_total;
    private $_shortPointsCount = 8;
    
    public function __construct($total = null)
    {
        if ($total !== null) {
            $this->setTotal($total);
        }
    }
    
    public function getPages2()
    {
        if (!$this->_size) {
            return null;
        }
        
        $pagesCount = ceil($this->_total/$this->_size);
        
        $pagesCount = 230000;
        $l = 20;
        
        $b = min(2, pow($pagesCount, 1/$l));
        
        echo "\npagesCount = $pagesCount";
        echo "\nb = $b";
        
        $points = array();
        
        for ($i=0; $i<=$l; $i++) {
            $p = round(pow($b, $i));
            $p = self::roundMantissa($p, 0);
            $points[$p] = $p;
        }
        echo "\n". print_r($points, 1) ."\n";
    }
    
    public function getPages()
    {
        if (!$this->_size) {
            return null;
        }
        
        $pagesCount = ceil($this->_total/$this->_size);
        
        $points = array();
        $step = pow(10, self::exponent10($pagesCount));
        $beginRange = array();
        $endRange = array();
        $base = 2;//3
        $beginRange[$step] = 0;
        $endRange[$step] = $pagesCount;
        for (; $step>=1; $step = $step/10){
            for ($i = $beginRange[$step]; $i<=$endRange[$step]; $i += $step) {
                if (!isset($points[$i])) {
                    if (self::checkPoint($this->_page, $i, $base)) {
                        $points[$i] = $i;
                        $nextStep = $step/10;
                        if (!isset($beginRange[$nextStep])) {
                            $beginRange[$nextStep] = $pagesCount;
                        }
                        if (!isset($endRange[$nextStep])) {
                            $endRange[$nextStep] = 0;
                        }
                        $beginRange[$nextStep] = max(0, min($i-$step, $beginRange[$nextStep]));
                        $endRange[$nextStep] = min($pagesCount, max($i+$step, $endRange[$nextStep]));
                    }
                }
            }
        }
        
        //current decade
        $beginPoint = floor($this->_page/10)*10;
        $endPoint = min($pagesCount, $beginPoint + 9);
        for ($i = $beginPoint; $i<=$endPoint; $i++) {
            $points[$i] = $i;
        }

        if (!isset($points[1])) {
            $points[1] = 1;
        }
        
        if (!isset($points[$pagesCount])) {
            $points[$pagesCount] = $pagesCount;
        }
        unset($points[0]);
        sort($points);
        $allpoints = array();
        for ($i=0; $i<count($points); $i++){
            if ($i>0) {
               $subpoints = self::chunkRange($points[$i-1], $points[$i], $this->_shortPointsCount);
               for ($j=0; $j<count($subpoints); $j++){
                   $allpoints[] = array('short' => true, 'number' => $subpoints[$j]);
               }
            }
            $allpoints[] = array('short' => false, 'number' => $points[$i]);
        }
        

        $pages = array();
        foreach ($allpoints as $key=>$point) {
            $newOffset = ($point['number']-1) * $this->_size;
            $pages[$point['number']] = array(
                'newOffset' => $newOffset,
                'pageNum' => $newOffset,
                'short' => $point['short']
            );
        }
        
        return array(
            'pageCount' => $pagesCount,
            'pages' => $pages,
            'previous' => $this->_page>1? $this->_page - 1 : null,
            'next' => $this->_page<$pagesCount? $this->_page + 1 : null,
            'current' => $this->_page
        );
    }
   
    /**
     * Определение порядка числа (exponent10(1.45 E+7) = 7)
     */
    static public function exponent10($x)
    {
        if ($x<=0) return 0;
        return floor(log($x, 10));
    }
    
    static public function p($x, $i, $base)
    {
        if ($x==$i) return 0;
        return ceil(log(abs($x-$i), $base));
    }
    
    
    static public function r($x, $base)
    {
        return pow($base, ceil(log($x, $base)));
    }

    static public function checkPoint($currentPage, $i, $base)
    {
        $y = self::p($currentPage, $i, $base) - 1;
        $level = self::r(pow($base, $y), 10);
        if (($level<1) || !($i % $level)) {
            return true;
        }
        return false;
    }    

    /**
     * Округляет мантиссу числа (roundMantissa(1.45 E+10) = 1 E+10)
     */
    static public function roundMantissa($x, $precision = 0)
    {
        $numberExponent = pow(10, floor(log($x, 10)));
        return $numberExponent * round($x / $numberExponent, $precision);
    }
    /**
     * Возвращает $count точек между числами $x1 и $x2
     */
    static public function chunkRange($x1, $x2, $count)
    {
        $n = $x2 - $x1;
        $count = $count + 1;
        if ($n<$count) return array();
        $partSize =  self::roundMantissa($n/$count);
        $points = array();
        $currentPoint = $x1;
        while ($currentPoint<($x2-$partSize)) {
            $currentPoint += $partSize;
            $precision = self::exponent10($currentPoint) - self::exponent10($partSize);
            $points[] = self::roundMantissa($currentPoint, $precision);
        }
        return $points;
    }
    
    static public function simplifyNumber($number, $style = self::SI_PREFIX)
    {
        static $symbols;
        if (!$symbols) {
            switch ($style) {
                case self::EXP_NOTATION:
                    $symbols[pow(10, 15)] = '&middot;10<sup>15</sup>';
                    $symbols[pow(10, 14)] = '&middot;10<sup>14</sup>';
                    $symbols[pow(10, 13)] = '&middot;10<sup>13</sup>';
                    $symbols[pow(10, 12)] = '&middot;10<sup>12</sup>';
                    $symbols[pow(10, 11)] = '&middot;10<sup>11</sup>';
                    $symbols[pow(10, 10)] = '&middot;10<sup>10</sup>';
                    $symbols[pow(10, 9)] = '&middot;10<sup>9</sup>';
                    $symbols[pow(10, 8)] = '&middot;10<sup>8</sup>';
                    $symbols[pow(10, 7)] = '&middot;10<sup>7</sup>';
                    $symbols[pow(10, 6)] = '&middot;10<sup>6</sup>';
                    $symbols[pow(10, 5)] = '&middot;10<sup>5</sup>';
                    $symbols[pow(10, 4)] = '&middot;10<sup>4</sup>';
                    $symbols[pow(10, 3)] = '&middot;10<sup>3</sup>';
                break;
                case self::SI_PREFIX:
                    $symbols[pow(10, 15)] = 'P';
                    $symbols[pow(10, 12)] = 'T';
                    $symbols[pow(10, 9)] = 'G';
                    $symbols[pow(10, 6)] = 'M';
                    $symbols[pow(10, 3)] = 'k';
                break;
                
            }
        }
        foreach ($symbols as $decimalMultipler => $symbol) {
            if ($number < $decimalMultipler) {
                continue;
            }
            $newNumber = $number / $decimalMultipler . $symbol;
            if (strlen($newNumber)<=strlen($number)) {
                return $newNumber;
            }
/*            if (!($number % $decimalMultipler)) {
                return $number / $decimalMultipler . $symbol;
            }*/
        }
        return $number;
    }
    
    public function setItemCountPerPage($size)
    {
        $this->_size = $size;
        return $this;
    }
    
    public function setCurrentPageNumber($page)
    {
        $this->_page = $page;
        return $this;
    }
    
    public function setOffset($offset)
    {
        if ($this->_size===null) {
            throw new Lms_Exception('$size is unknown, call setItemCountPerPage before.');
        }
        
        $this->_page = ceil(($offset+1)/$this->_size);
        return $this;
    }

    public function setTotal($total)
    {
        $this->_total = $total;
        return $this;
    }
}