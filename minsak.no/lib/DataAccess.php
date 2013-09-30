<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php

/**
 * Define a custom exception class
 */
class DataAccessException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0) {
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }
    
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function customFunction() {
        echo "A Custom function for this type of exception\n";
    }
}

/**
 * A table does not exist in database
 */
class TableDoesNotExistException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0) {
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }
    
    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
}

/**
 * Data access class provides access to database
 * data can be returned as DataAccessResults
*/
class DataAccess
{
	
	
    /**
     * @var mysqli
     */
    private $db = null; // database resource link
    private $lockedTables = array(); // Key: classname, Value: array with ('locktype' => LOCKTYPE_READ or LOCKTYPE_WRITE, 'alias' => optional table alias)
    
    /**
     * @var AppContext
     */
    private $app = null;

    const LOCKTYPE_READ  = 'READ';
    const LOCKTYPE_WRITE = 'WRITE';

    public function __construct(AppContext $app)
    {
        $this->app = $app;
        $this->connect();
    }
    
    public function __destruct()
    {
        if ($this->db !== null)
            $this->db->close();
            $this->db = null;
    }
    /**
    * Connect to database
    * Constants must be defined
    */
    protected function connect()
    {
        //$this->log("Connecting to db: host=" . DB_HOST . ", user=" . DB_USER . ", pass=" . DB_PASS . ", database=" . DB_NAME, "INFO");
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$this->db || $this->db->connect_error) {
            $this->db = null;
            throw new DataAccessException("DB error:" . mysqli_connect_error(), mysqli_connect_errno());
        }
        $this->db->set_charset('utf8');
    }


    /**
    * Perform a query
    *
    * @param String $sql the sql string
    * @return row[] or true or false
    * @throws TableDoesNotExistException if table does not exist
    * @throws DataAccessException if an error occurs
    */
    public function query($sql)
    {
        if (LOG_SQL) {
        $this->app->log("query: " . $sql);
        }
        try {
            $result = $this->db->query($sql);
            if ($result === false) {
                throw new DataAccessException("DB error ($sql): " . $this->db->error,$this->db->errno);
            }
            elseif ($result === true) {
                return true;
            } else {
                return $result;
            }
        } catch (Exception $e) {
            $this->app->log('Caught exception: ' .  $e->getMessage() . " " .  $e->getCode(), "ERROR");

            if ($e->getCode() == 1146) {
                // table does not exist
                throw new TableDoesNotExistException($e->getMessage(),$e->getCode());
            } else {
                throw new DataAccessException("DB error ($sql): " . $e->getMessage(),$e->getCode());
            }
        }
    }


    /**
     * quickly fetch all data
     *
     * @param String $sql the sql string
     * @return Array or false if no result was returned
     */
    public function fetchAll($sql)
    {
        $res = $this->query($sql);
        if ($res) {
            $rows = array();
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        } else {
            return false;
        }
    }

    /**
     * quickly fetch all data, with array keys from a named column
     *
     * @param String $sql the sql string
     * @param String $key_column_name name of the column to use for array keys, for example 'id'
     * @return Array or false if no result was returned
     */
    public function fetchAllKeyed($sql, $key_column_name)
    {
        $res = $this->query($sql);
        if ($res) {
            $rows = array();
            while ($row = $res->fetch_assoc()) {
                $rows[$row[$key_column_name]] = $row;
            }
            return $rows;
        } else {
            return false;
        }
    }
    
    /**
     * Get last id from an insert on auto_increment
     * @return int the id
     */
    public function lastInsertID()
    {
        return $this->db->insert_id;
    }

    /**
     * Escape a value for sql insertion
     * @param String $str
     * @return string the escaped string
     */
    public function escape($str)
    {
        if(is_string($str)) {
            return $this->db->escape_string($str);
        } else {
            return $str;
        }
    }
    
    /**
     * Escape an array of values. escape() is called on each value, and it is wrapped in ''
     * @param array $array array of values
     */
    public function escapeArray(Array $array) {
        $result = Array();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $value) {
                $result[] = "'" . $this->escape($value) . "'";
            }
        }
        return $result;
    }

    /**
     * Utility function to build an insert sql string
     * @param String $tableName the name of the table to insert into
     * @param array $values associative array containing the values to insert
     * @return string the insert sql string
     */
    public function buildInsertSql($tableName, Array $values) {
        $nameArray = Array();
        $valueArray = Array();
        foreach ($values as $name => $value) {
            $nameArray[] = '`' . $name . '`';
            $valueArray[] = "'" . $this->escape($value) . "'";
        }
        $sql = "insert into `" . $tableName . "` (" . join(',', $nameArray) . ") values (" . join(',', $valueArray) . ")";
        return $sql;
    }
    
    /**
     * Utility function to build an update sql string
     * @param String $tableName the name of the table to update
     * @param array $values associative array containing the values to update
     * @param array $whereConditions associative array containing constraints for the update (typically Array('id' => $id))
     * @return string the update sql string
     */
    public function buildUpdateSql($tableName, Array $values, Array $whereConditions) {
        $setArray = Array();
        foreach ($values as $name => $value) {
            $setArray[] = '`' . $name . "`='" . $this->escape($value) . "'";
        }
        $whereConditionArray = Array();
        foreach ($whereConditions as $name => $value) {
            $whereConditionArray[] = '`' . $name . "`='" . $this->escape($value) . "'"; 
        }
        
        $sql = "update `" . $tableName . "` set " . join(',', $setArray) . " WHERE " . join(' AND ', $whereConditionArray);
        return $sql;
    }
    
    /**
     * Locks the specified tables
     * @param array $tableArray associative array where key is classname, value is array: ('locktype' => 'read' or 'write', 'alias' => table alias (optional))
     */
    public function lockTables(array $tableArray)
    {
        if (!empty($this->lockedTables)) {
            throw new DataAccessException('Tables already locked');
        }

        if (count($tableArray) == 0) {
            throw new DataAccessException('Must specify at least one table to lock');
        }

        $locks = array();
        foreach ($tableArray as $lockinfo) {
            if (!is_array($lockinfo)) {
                throw new DataAccessException('Lock info value for ['.$tablename.'] was not an array');
            }
            if (!array_key_exists('locktype', $lockinfo)) {
                throw new DataAccessException('Lock info value for ['.$tablename.'] did not contain an entry for "locktype"');
            }
            if (!array_key_exists('tablename', $lockinfo)) {
                throw new DataAccessException('Lock info value for ['.$tablename.'] did not contain an entry for "tablename"');
            }
            $locktype = $lockinfo['locktype'];
            if ($locktype != self::LOCKTYPE_READ && $locktype != self::LOCKTYPE_WRITE) {
                throw new DataAccessException('Invalid locktype specified');
            }
            $tablename = $lockinfo['tablename'];
            $alias = $tablename;
            if (array_key_exists('alias', $lockinfo)) {
                $alias = $lockinfo['alias'];
            }
            $locks[] = '`'.$this->escape($tablename).'` AS `'.$this->escape($alias).'` '.$locktype;
        }

        $this->query('LOCK TABLES '.join(',',$locks));
        $this->lockedTables = $tableArray;
    }

    /**
     * Unlocks all tables that are locked by this process
     * @throws DataAccessException if no tables are locked
     */
    public function unlockTables()
    {
        if (empty($this->lockedTables)) {
            throw new DataAccessException('No tables locked');
        }
        $this->query('UNLOCK TABLES');
        $this->lockedTables = array();
    }

    /**
     * Unlocks all tables that are locked by this process (this method will not throw exception if no tables are locked)
     */
    public function ensureTablesUnlocked()
    {
        if (!empty($this->lockedTables)) {
            $this->unlockTables();
        }
    }

    /**
     * Get table lock status
     * @return true if some tables are locked, false otherwise
     */
    public function isTablesLocked()
    {
        return !(empty($this->lockedTables));
    }

    /**
     * Get the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query.
     * If the last query was invalid, this function will return -1.
     * Only works with queries which modify a table.
     *
     * @return integer An integer greater than zero indicates the number of rows affected or retrieved. Zero indicates that no records where updated for an UPDATE statement, no rows matched the WHERE clause in the query or that no query has yet been executed. -1 indicates that the query returned an error.
     */
    public function affectedRows() {
        return $this->db->affected_rows;
    }

    /**
     * Get a string representation of this object
     * @return string
     */
    public function __toString() {
        return "DataAccess@" . $db_host;
    }
}
