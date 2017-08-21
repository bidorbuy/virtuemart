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
 * Class Db
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 */
abstract class Db {
    const SETTING_PREFIX = 'prefix';
    const SETTING_SERVER = 'server';
    const SETTING_USER = 'user';
    const SETTING_PASS = 'pass';
    const SETTING_DBNAME = 'dbname';

    /**
     * Resource link
     */
    protected $link;

    /**
     * Server (eg. localhost)
     */
    protected $server;

    /**
     * Database user (eg. root)
     */
    protected $user;

    /**
     * Database password (eg. can be empty !)
     */
    protected $password;

    /**
     * Database name
     */
    protected $database;

    /**
     * SQL cached result
     */
    protected $result;

    /**
     * Logger instance
     */
    public $logger;

    /**
     * DB instance
     */
    protected static $instance;

    /**
     * Instantiate database connection
     *
     * @param string $server   Server address
     * @param string $user     User login
     * @param string $password User password
     * @param string $database Database name
     * @param bool   $connect  If false, don't connect in constructor (since 1.5.0)
     *
     * @return Db
     */
    public function __construct($server, $user, $password, $database, $connect) {
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->logger = new Logger(new Settings());
        if ($connect) {
            $this->connect();
        }
    }

    /**
     * Destruct
     *
     * @return void
     */
    public function __destruct() {
        if ($this->link) {
            $this->disconnect();
        }
    }

    /**
     * Open a connection
     *
     * @return resource
     */
    abstract public function connect();

    /**
     * Close a connection
     *
     * @return void
     */
    abstract public function disconnect();

    /**
     * Useful for some modules
     *
     * @param string $dbName database name
     *
     * @return mixed
     */
    abstract public function setDb($dbName);

    /**
     * Execute a query and get result resource
     *
     * @param string $sql        query
     * @param bool   $unbuffered query type
     *
     * @return mixed
     */
    public function query($sql, $unbuffered = NULL) {
        if (!$this->link) {
            $this->connect();
        }

        if ($error = $this->getLastError()) {
            $error = 'Database Error: ' . $error;
            $this->logger->error($error . " SQL query: $sql");
            echo $error . '<br>';
        }

        $this->result = $this->queryInternal($sql, $unbuffered);

        return $this->result;
    }

    /**
     * Execute a query and get result resource
     *
     * @param string $sql        query
     * @param bool   $unbuffered query type
     *
     * @return mixed
     */
    abstract protected function queryInternal($sql, $unbuffered = NULL);

    /**
     * Execute a query
     *
     * @param string $sql query
     *
     * @return bool
     */
    public function execute($sql) {
        if (!$this->link) {
            $this->connect();
        }

        $this->result = $this->query($sql);

        return $this->result;
    }

    /**
     * ExecuteS return the result of $sql as array
     *
     * @param string  $sql   query to execute
     * @param boolean $array return an array instead of a mysql_result object
     *                       (deprecated since 1.5.0, use query method instead)
     *
     * @return array or result object
     */
    public function executeS($sql, $array) {
        if (!$this->link) {
            $this->connect();
        }

        // This method must be used only with queries which display results
        if (!preg_match('#^\s*\(?\s*(select|show|explain|describe|desc)\s#i', $sql)) {
            return $this->execute($sql);
        }

        $this->result = FALSE;
        $this->result = $this->query($sql);

        if (!$this->result) {
            return FALSE;
        }

        if (!$array) {
            return $this->result;
        }

        $result = array();
        while ($row = $this->nextRow($this->result)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Get next row for a query which doesn't return an array
     *
     * @param mixed $result result
     *
     * @return mixed
     */
    abstract public function nextRow($result = NULL);

    /**
     * Sanitize data which will be injected into SQL query
     *
     * @param string  $string  SQL data which will be injected into SQL query
     * @param boolean $html_ok Does data contain HTML code ? (optional)
     *
     * @return string Sanitized data
     */
    public function escape($string, $html_ok) {
        if (!$this->link) {
            $this->connect();
        }

        if (!is_numeric($string)) {
            $string = $this->escapeInternal($string);
            if (!$html_ok) {
                $string = strip_tags($this->nl2br($string));
            }
        }

        return $string;
    }

    /**
     * Convert \n and \r\n and \r to <br />
     *
     * @param string $str String to transform
     *
     * @return string New string
     */
    private function nl2br($str) {
        return str_replace(array("\r\n", "\r", "\n"), '<br />', $str);
    }

    /**
     * Throw error function
     *
     * @param string $message error message
     *
     * @throws \Exception
     *
     * @return void
     */
    public function throwError($message) {
        throw new \Exception($message);
    }

    /**
     * Remove FULL_GROUP_BY mode
     *
     * @return void
     */
    protected function removeFullGroupByMode() {
        $this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }

    /**
     * Protect string against SQL injections
     *
     * @param string $str string to escape
     *
     * @return string
     */
    abstract public function escapeInternal($str);

    /**
     * Get Last DB Error
     *
     * @return string
     */
    abstract public function getLastError();
}
