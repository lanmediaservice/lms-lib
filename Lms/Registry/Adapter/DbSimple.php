<?php
class Lms_Registry_Adapter_DbSimple implements Lms_Registry_Adapter_Interface {
    
    private $_masterDb;
    private $_slaveDb;
    private $_tableName;
    private $_keyColumn = 'key';
    private $_valueColumn = 'value';
    
    public function __construct($tableName = null, DbSimple_Generic_Database $masterDb = null, DbSimple_Generic_Database $slaveDb = null)
    {
        if ($tableName) {
            $this->setTableName($tableName);
        }
        
        if ($masterDb) {
            $this->setDb($masterDb, $slaveDb);
        }
    }
    
    /**
     * Sets master database and optionaly slave database 
     *
     * @param DbSimple_Generic_Database $masterDb
     * @param DbSimple_Generic_Database $slaveDb
     */
    
    public function setDb(DbSimple_Generic_Database $masterDb, DbSimple_Generic_Database $slaveDb = null)
    {
        $this->_masterDb = $masterDb;
        $this->_slaveDb = ($slaveDb == null)? $masterDb : $slaveDb;
        return $this;
    }
    
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;
        return $this;
    }
    
    public function setKeyColumn($keyColumn)
    {
        $this->_keyColumn = $keyColumn;
        return $this;
    }
    
    public function setValueColumn($valueColumn)
    {
        $this->_valueColumn = $valueColumn;
        return $this;
    }
    
    public function get($key, $default = null)
    {
        $result = $this->_slaveDb->selectCell("SELECT ?# FROM {$this->_tableName} WHERE ?# = ?", $this->_valueColumn, $this->_keyColumn, $key);
        if (!isset($result)) {
            return $default;
        } else {
            return $result;
        }
    }
    
    public function set($key, $value)
    {
        $this->_masterDb->query("INSERT INTO {$this->_tableName} SET ?#=?, ?#=? ON DUPLICATE KEY UPDATE ?#=?", $this->_keyColumn, $key, $this->_valueColumn, $value, $this->_valueColumn, $value);
        return $this;
    }
}
?>