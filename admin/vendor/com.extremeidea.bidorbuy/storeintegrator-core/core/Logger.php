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


use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NativeMailerHandler;

/**
 * Class Logger.
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 */
class Logger {

    const LOGGER_NAME = 'bobsi';

    private $settings;

    protected $logger = NULL;

    /**
     * Logger constructor.
     *
     * @param Settings $settings settings
     *
     * @return mixed
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * Get logger instance.
     *
     * @return MonologLogger
     */
    public function getLogger() {

        if (!$this->logger) {
            $logName = Settings::$logsPath . '/' . sprintf("bobsi_%s.log", date('Y-m-d'));
            $loggingLevel = $this->getLoggingLevel();
            $this->logger = new MonologLogger(self::LOGGER_NAME);
            $this->logger->pushHandler(new StreamHandler($logName, $loggingLevel));

            return $this->logger;
        }

        return $this->logger;
    }

    /**
     * Get Logging level
     *
     * @return int
     */
    protected function getLoggingLevel() {
        $level = $this->settings->getLoggingLevel();

        switch ($level) {
            case 'all':
                $loggerLevel = MonologLogger::INFO;
                break;
            case 'fatal':
                $loggerLevel = MonologLogger::CRITICAL;
                break;
            case 'error':
                $loggerLevel = MonologLogger::ERROR;
                break;
            case 'warn':
                $loggerLevel = MonologLogger::WARNING;
                break;
            case 'info':
                $loggerLevel = MonologLogger::INFO;
                break;
            case 'debug':
                $loggerLevel = MonologLogger::DEBUG;
                break;
            case 'trace':
                $loggerLevel = MonologLogger::INFO;
                break;
            default:
                $loggerLevel = MonologLogger::INFO;
                break;
        }

        return $loggerLevel;
    }

    /**
     * Proxy function
     *
     * @param string $level legger function name etc: crit, err, warn for monolog v1.0
     * @param string $message message to log
     *
     * @return void
     */
    protected function log($level, $message) {
        $logger = $this->getLogger();
        $logger->$level($message);
    }

    /**
     * Log fatal message
     *
     * @param string $message message to log
     *
     * @return void
     */
    public function fatal($message) {
        $this->log('critical', $message);
    }

    /**
     * Log error message
     *
     * @param string $message message to log
     *
     * @return void
     */
    public function error($message) {
        $this->log('error', $message);
    }

    /**
     * Log warning message
     *
     * @param string $message message to log
     *
     * @return void
     */
    public function warning($message) {
        $this->log('warning', $message);
    }

    /**
     * Log info message
     *
     * @param string $message message to log
     *
     * @return void
     */
    public function info($message) {
        $this->log('info', $message);
    }

    /**
     * Log debug message
     *
     * @param string $message message to log
     *
     * @return void
     */
    public function debug($message) {
        $this->log('debug', $message);
    }
}
