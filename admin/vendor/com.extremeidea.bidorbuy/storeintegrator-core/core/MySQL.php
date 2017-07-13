<?php

/**
 * Copyright (c) 2014, 2015, 2016 Bidorbuy http://www.bidorbuy.co.za
 * This software is the proprietary information of Bidorbuy.
 *
 * All Rights Reserved.
 * Modification, redistribution and use in source and binary forms, with or without
 * modification are not permitted without prior written approval by the copyright
 * holder.
 *
 * Vendor: EXTREME IDEA LLC http://www.extreme-idea.com
 */

namespace com\extremeidea\bidorbuy\storeintegrator\core;

/**
 * Deprecated
 *
 * @deprecated mysql extension was officially deprecated in PHP v5.5.0 and will
 *     be removed in PHP v7. http://php.net/manual/en/changelog.mysql.php we
 *     need to mute fatal deprecation errors.
 */

/**
 * Class MySQL
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 */
class MySQL extends Db {

    /**
     * Open a connection
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function connect() {
        if (!extension_loaded('mysql')) {
            $message = Version::$name . ' error: The "mysql" required PHP extension is not installed or loaded. It is required to be installed.';
            $this->throwError($message);
        }

        if (!$this->link = mysql_connect($this->server, $this->user, $this->password)) {
            $message = 'Link to database cannot be established.';
            $this->throwError($message);
        }

        if (!$this->setDb($this->database)) {
            $message = 'The database selection cannot be made.';
            $this->throwError($message);
        }

        if ($this->link) {
            $this->removeFullGroupByMode();
        }

        return $this->link;
    }

    /**
     * Close a connection
     *
     * @return void
     */
    public function disconnect() {
        if ($this->link) {
            mysql_close($this->link);
        }
    }

    /**
     * Set database
     *
     * @param string $dbName database name
     *
     * @return bool
     */
    public function setDb($dbName) {
        return ($this->link) ? mysql_select_db($dbName, $this->link) : false;
    }

    /**
     * Execute a query and get result resource
     *
     * @param string $sql query
     * @param bool $unbuffered query type
     *
     * @return mixed
     */
    protected function queryInternal($sql, $unbuffered = null) {
        return ($unbuffered) ? mysql_unbuffered_query($sql, $this->link) : mysql_query($sql, $this->link);
    }

    /**
     * Get next row for a query which doesn't return an array
     *
     * @param mixed $result query result
     *
     * @return array
     */
    public function nextRow($result = null) {
        $return = false;

        if (is_resource($result) && $result) {
            $return = mysql_fetch_assoc($result);
        } elseif (is_resource($this->result) && $this->result) {
            $return = mysql_fetch_assoc($this->result);
        }

        return $return;
    }

    /**
     * Protect string against SQL injections
     *
     * @param string $str string to escape
     *
     * @return string
     */
    public function escapeInternal($str) {
        if ($this->link) {
            return function_exists('mysql_real_escape_string') ? mysql_real_escape_string($str, $this->link) : addslashes($str);
        }

        return false;
    }

    /**
     * Get last DB error
     *
     * @return mixed
     */
    public function getLastError() {
        return mysql_error($this->link);
    }
}
