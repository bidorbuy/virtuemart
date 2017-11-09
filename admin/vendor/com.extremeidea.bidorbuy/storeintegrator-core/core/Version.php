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

if (class_exists('\com\extremeidea\bidorbuy\storeintegrator\core\Version', FALSE)) {
    return;
}

/**
 * Class Version
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Version {
    public static $platform = '';

    public static $id = 'bidorbuystoreintegrator';
    public static $version = '2.0.11.20171108215107.8d9ed2a8';
    public static $coreVersion = '1.1.16.20171108135759.c1840d8c';
    public static $name = 'bidorbuy Store Integrator';
    public static $description = 'The bidorbuy store integrator allows you to get products from your online store 
    listed on bidorbuy quickly and easily.';

    public static $author = 'bidorbuy';
    public static $authorUrl = 'www.bidorbuy.co.za';

    /**
     * Get current plugin version
     *
     * @return mixed
     */
    public static function getLivePluginVersion() {
        $version = array();

        if (!empty(Version::$platform)) {
            $version[] = Version::$platform;
        }

        $version[] = trim(Version::$name . ' ' . Version::$version);
        $version[] = trim('core ' . Version::$coreVersion);

        return implode(', ', $version);
    }

    /**
     * Get environment options
     *
     * @return array
     */
    public static function getMetrics() {
        $value = array();

        $value['plugin.version'] = self::getLivePluginVersion();

        $value['php.version'] = self::getPhpVersion();
        $value['php.memory_limit'] = ini_get('memory_limit');
        $value['php.safe_mode'] = ini_get('safe_mode');
        $value['php.open_basedir'] = ini_get('open_basedir');
        $value['php.zlib.output_compression'] = ini_get('zlib.output_compression');
        $value['curl.version'] = curl_version(); // PHP 4 >= 4.0.2, PHP 5

        if (defined('WP_MEMORY_LIMIT')) {
            $value['wp.memory_limit'] = WP_MEMORY_LIMIT;
        }

        return $value;
    }

    /**
     * Get php version
     *
     * @return mixed
     */
    public static function getPhpVersion() {
        return phpversion();
    }

    /**
     * Get only version from string
     *
     * @param string $str string which contains plugin version
     *
     * @return string version
     */
    public static function getVersionFromString($str) {
        $pattern = '/\d+(?:\.\d+)+/';
        preg_match($pattern, $str, $version);

        return $version[0];
    }
}
