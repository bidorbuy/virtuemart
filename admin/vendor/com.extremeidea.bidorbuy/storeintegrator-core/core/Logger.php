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

use com\extremeidea\php\tools\log4php as log4php;

// Patch for Joomla 1.5 https://issues.apache.org/jira/browse/LOG4PHP-129
if (function_exists('__autoload')) {
    define('WARN_ON_AUTOLOAD_IGNORE', true);
    spl_autoload_register('__autoload');
}

class LoggerAppenderDailyFile extends log4php\LoggerAppenderDailyFile {
    public function __construct() {
    }

    public function setFile($file) {
        parent::setFile(Settings::$logsPath . '/' . $file);
    }
}

class LoggerAppenderMail extends log4php\LoggerAppenderMail {
    public function setSubject($subject = '') {
        $subject = Settings::$storeName . ' - ' . Version::$name;
        parent::setSubject($subject);
    }

    public function setFrom($from = '') {
        $from = Settings::$storeEmail;
        parent::setFrom($from);
    }

    public function close() {
        if ($this->closed != true) {
            $from = $this->from;
            $sendTo = $this->to;

            if (!empty($this->body) and $from !== null and $sendTo !== null and $this->layout !== null) {
                if (!$this->dry) {
                    $message = $this->layout->getHeader() . $this->body . $this->layout->getFooter();
                    $subject = $this->subject;
                    $contentType = $this->layout->getContentType();

                    $headers = "From: {$from}\r\n";
                    $headers .= "Content-Type: {$contentType}\r\n";

                    mail($sendTo, $subject, $message, $headers);
                } elseif ($this->dry) {
                    echo "DRY MODE OF MAIL APP.: Send mail to: " . $sendTo . " with content: " . $this->body;
                }
            }
            $this->closed = true;
        }
    }
}

class Logger {
    private $settings;
    private static $configured = false;

    public function __construct(Settings $settings) {
        $this->settings = $settings;

        if (self::$configured === false) {
            self::$configured = true;
            log4php\Logger::configure(dirname(__FILE__) . '/log4php.xml');
        }
    }

    public function getLogger($name) {
        static $loggers;

        if (!$loggers) {
            $loggers = array();
        }

        if (!isset($loggers[$name])) {
            $loggers[$name] = log4php\Logger::getLogger($name);
        }

        $logger = &$loggers[$name];
        $level = $this->settings->getLoggingLevel();

        switch ($level) {
            case 'all':
                $logger->setLevel(log4php\LoggerLevel::getLevelAll());
                break;
            case 'fatal':
                $logger->setLevel(log4php\LoggerLevel::getLevelFatal());
                break;
            case 'error':
                $logger->setLevel(log4php\LoggerLevel::getLevelError());
                break;
            case 'warn':
                $logger->setLevel(log4php\LoggerLevel::getLevelWarn());
                break;
            case 'info':
                $logger->setLevel(log4php\LoggerLevel::getLevelInfo());
                break;
            case 'debug':
                $logger->setLevel(log4php\LoggerLevel::getLevelDebug());
                break;
            case 'trace':
                $logger->setLevel(log4php\LoggerLevel::getLevelTrace());
                break;
        }

        return $logger;
    }

    public function log($level, $message) {
        $localFileLogger = $this->getLogger('localFileLogger');

        if (!class_exists('com\extremeidea\php\tools\log4php\LoggerLoggingEvent')) {
            log4php\LoggerAutoloader::autoload('com\extremeidea\php\tools\log4php\LoggerLoggingEvent');
        }

        if (!class_exists('com\extremeidea\php\tools\log4php\LoggerNDC')) {
            log4php\LoggerAutoloader::autoload('com\extremeidea\php\tools\log4php\LoggerNDC');
        }

        call_user_func_array(array($localFileLogger, $level), array($message));

        $logging_email_notifications = $this->settings->getEnableEmailNotifications();
        $logging_email_notifications_addresses = $this->settings->getEmailNotificationAddresses();

        if ($logging_email_notifications && !empty($logging_email_notifications_addresses)) {
            $localEmailLogger = $this->getLogger('localEmailLogger');

            $appender = $localEmailLogger->getAppender('appenderMail');
            $appender->setTo($logging_email_notifications_addresses);

            $appender->setFrom();
            $appender->setSubject();

            call_user_func_array(array($localEmailLogger, $level), array($message));
        }
    }

    public function fatal($message) {
        $this->log('fatal', $message);
    }

    public function error($message) {
        $this->log('error', $message);
    }

    public function warning($message) {
        $this->log('warn', $message);
    }

    public function info($message) {
        $this->log('info', $message);
    }

    public function debug($message) {
        $this->log('debug', $message);
    }

    public function trace($message) {
        $this->log('trace', $message);
    }
}
