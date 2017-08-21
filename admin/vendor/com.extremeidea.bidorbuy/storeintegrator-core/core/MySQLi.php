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
 * Class MySQLi
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 */
class MySQLi extends Db {

    /**
     * Get mysql instance
     * Set DB settings
     * Function for override and use in tests
     *
     * @param string $server   server
     * @param string $user     user
     * @param string $password password
     * @param string $database db name
     * @param string $port     port
     *
     * @return \mysqli
     */
    public function getDbInstance($server, $user, $password, $database, $port) {
        return @new \mysqli($server, $user, $password, $database, $port);
    }

    /**
     * Open a connection
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function connect() {
        if (!extension_loaded('mysqli')) {
            $message = Version::$name
                . ' error: The "mysqli" required PHP extension is not installed or loaded. 
                It is required to be installed.';
            $this->throwError($message);
        }

        $server = $this->server;
        $port = ini_get("mysqli.default_port");

        if (strpos($this->server, ':') !== FALSE) {
            list($server, $port) = explode(':', $this->server);
        }

        $this->link = $this->getDbInstance($server, $this->user, $this->password, $this->database, $port);

        if (mysqli_connect_error()) {
            $message = sprintf('Link to database cannot be established: %s', mysqli_connect_error());
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
            $this->link->close();
        }
    }

    /**
     * Useful for some modules
     *
     * @param string $dbName database name
     *
     * @return mixed
     */
    public function setDb($dbName) {
        return ($this->link) ? $this->link->query('USE ' . $this->escape($dbName, FALSE)) : FALSE;
    }

    /**
     * Execute a query and get result resource
     *
     * @param string $sql        query
     * @param bool   $unbuffered query type
     *
     * @return mixed
     */
    protected function queryInternal($sql, $unbuffered = NULL) {
        return ($unbuffered) ? $this->link->query($sql, MYSQLI_USE_RESULT) : $this->link->query($sql);
    }

    /**
     * Get next row for a query which doesn't return an array
     *
     * @param mixed $result query result
     *
     * @return array
     */
    public function nextRow($result = NULL) {
        if (!is_object($result)) {
            return FALSE;
        }

        return $result->fetch_assoc();
    }

    /**
     * Protect string against SQL injections
     *
     * @param string $str string
     *
     * @return string
     */
    public function escapeInternal($str) {
        return ($this->link) ? $this->link->real_escape_string($str) : FALSE;
    }

    /**
     * Get last DB error
     *
     * @return mixed
     */
    public function getLastError() {
        return mysqli_error($this->link);
    }


}
