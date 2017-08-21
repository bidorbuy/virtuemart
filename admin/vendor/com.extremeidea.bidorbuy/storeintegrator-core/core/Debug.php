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

class Debug {
    const LOG_TYPE_JSON = 'json';
    const LOG_TYPE_PRINTR = 'pr';
    const LOG_TYPE_PLAIN = 'plain';

    const LOG_MODE_APPEND = 'append';
    const LOG_MODE_RANDOM = 'random';
    const LOG_MODE_NEW = 'new';

    public $location = NULL;

    /**
     * Writes debug information to temporary log file.
     *
     * @param string $value value to log.
     * @param string $mode  write mode. One of the following: 'append' (default),
     *                      'new', 'random'
     * @param string $type  type of data to write. One of the following: 'json'
     *                      (default, json_encode), 'pr' (print_r), otherwise the data is written as
     *                      is.
     *
     * @return mixed
     */
    public function log($value, $mode = self::LOG_MODE_APPEND, $type = self::LOG_TYPE_JSON) {
        $file = (empty($this->location) ? dirname(__FILE__) : $this->location) . '/debug';
        if ($mode == self::LOG_MODE_RANDOM) {
            $file .= '_' . time();
        }
        $file .= '.log';

        switch ($type) {
            case self::LOG_TYPE_JSON:
                $value = json_encode($value);
                break;
            case self::LOG_TYPE_PRINTR :
                $value = print_r($value, TRUE);
                break;
            default:
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                break;
        }
        $value .= "\n";

        $flags = NULL;
        if ($mode == self::LOG_MODE_APPEND) {
            $flags = FILE_APPEND;
        }

        file_put_contents($file, $value, $flags);

        return $file;
    }

    /**
     * Converts string value to hex presentation.
     *
     * @param string $string    string to convert.
     * @param string $delimiter delimiter.
     *
     * @return string hex presentation.
     */
    public function string2hex($string, $delimiter = '\x') {
        $characters = str_split($string);

        $string = '';
        foreach ($characters as &$char) {
            $string .= $delimiter . dechex(ord($char));
        }

        return $string;
    }

    /**
     * Converts string value from hex presentation to string.
     *
     * @param string $hex       string to convert.
     * @param string $delimiter delimiter.
     *
     * @return string presentation.
     */
    public function hex2string($hex, $delimiter = '\x') {
        $string = '';

        $characters = explode($delimiter, $hex);
        foreach ($characters as &$char) {
            $string .= chr(hexdec($char));
        }

        return $string;
    }
}
