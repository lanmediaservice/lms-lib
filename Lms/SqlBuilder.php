<?php

class Lms_SqlBuilder
{
    private $_allowedOperators = array();
    private $_safeMode = true;

    public function parse($element)
    {
        if (is_scalar($element)) {
            return $this->escapeString($element);
        } else if (is_array($element)) {
            $arguments = reset($element);
            $operator = key($element);
            return $this->statement($operator, $arguments);
        }
        throw new Lms_SqlBuilder_Exception('Error while parse element');
//        $statements = array();
//        foreach ($struct as $operator => $arguments) {
//            if (is_int($operator)) {
//                $statements[] = self::escapeString($arguments);
//            } else {
//                $statements[] = self::statement($operator, $arguments);
//            }
//        }
//        return implode($glue, $statements);
    }

    public function statement($operator, $arguments)
    {
        if ($this->_safeMode && !in_array($operator, $this->_allowedOperators)) {
            throw new Lms_SqlBuilder_ForbiddenStatementException();
        }
        switch ($operator) {
            case 'ident':
                return $this->escapeIdentifier($arguments);
                break;
            case 'equal':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument=$secondArgument)";
                break;
            case 'notequal':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument!=$secondArgument)";
                break;
            case 'gt':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument>$secondArgument)";
                break;
            case 'egt':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument>=$secondArgument)";
                break;
            case 'lt':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument<$secondArgument)";
                break;
            case 'elt':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument<=$secondArgument)";
                break;
            case 'isnull':
                $argument = $this->parse($arguments[0]);
                return "($argument IS NULL)";
                break;
            case 'isnotnull':
                $argument = $this->parse($arguments[0]);
                return "($argument IS NOT NULL)";
                break;
            case 'like':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse($arguments[1]);
                return "($firstArgument LIKE $secondArgument)";
                break;
            case 'contain':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse(
                    '%' . $this->escapeLike($arguments[1]) . '%'
                );
                return "($firstArgument LIKE $secondArgument)";
                break;
            case 'notcontain':
                $firstArgument = $this->parse($arguments[0]);
                $secondArgument = $this->parse(
                    '%' . $this->escapeLike($arguments[1]) . '%'
                );
                return "($firstArgument NOT LIKE $secondArgument)";
                break;
            case 'and':
                $arguments = array_map(array($this, 'parse'), $arguments);
                return "(" . implode(' AND ', $arguments) . ")";
                break;
            case 'or':
                $arguments = array_map(array($this, 'parse'), $arguments);
                return "(" . implode(' OR ', $arguments) . ")";
                break;
            case 'in':
                $field = $this->parse($arguments[0]);
                $values = $this->parse($arguments[1]);
                return "($field IN ($values))";
                break;
            case 'notin':
                $field = $this->parse($arguments[0]);
                $values = $this->parse($arguments[1]);
                return "($field NOT IN ($values))";
                break;
            case 'list':
                $arguments = array_map(array($this, 'parse'), $arguments);
                return implode(", ", $arguments);
                break;
            case 'convert':
                $text = $this->parse($arguments[0]);
                $from = $arguments[1];
                //http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html
                $charsets = array('big5','dec8','cp850','hp8','koi8r','latin1',
                                  'latin2','swe7','ascii','ujis','sjis',
                                  'hebrew','tis620','euckr','koi8u','gb2312',
                                  'greek','cp1250','gbk','latin5','armscii8',
                                  'utf8','ucs2','cp866','keybcs2','macce',
                                  'macroman','cp852','latin7','cp1251','cp1256',
                                  'cp1257','binary','geostd8','cp932','eucjpms');
                if (!in_array($from, $charsets)) {
                    throw new Lms_SqlBuilder_Exception("Charset $from not supported");
                }
                $to = $this->parse($arguments[2]);
                return "CONVERT( _{$from} $text USING $to)";
                break;
            default:
                break;
        }
    }

    public function escapeString($string)
    {
        return "'" . mysql_escape_string($string) . "'";
    }

    public function escapeIdentifier($string)
    {
        return "`" . str_replace('`', '``', $string) . "`";
    }

    public function escapeFloat($floatValue)
    {
        return str_replace(',', '.', floatval($floatValue));
    }

    public function escapeLike($string)
    {
        return strtr($string, array('_' => '\_', '%' => '\%'));
    }
    
    public function allow ($operators)
    {
        $this->_allowedOperators = $operators;
        return $this;
    }

    public function setSafeMode($enabled)
    {
        $this->_safeMode = $enabled;
        return $this;
    }
}
