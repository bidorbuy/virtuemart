<?php

/**
 * Copyright (c) 2014, 2015, 2016 Bidorbuy http://www.bidorbuy.co.za
 * This software is the proprietary information of Bidorbuy.
 *
 * All Rights Reserved.
 * Modification, redistribution and use in source and binary forms, with or without modification
 * are not permitted without prior written approval by the copyright holder.
 *
 * Vendor: EXTREME IDEA LLC http://www.extreme-idea.com
 */

/*
 * This file is deprecated and used only in Joomla 1.5.x.
 * It will be removed in 2017.
 */

// No direct access to this file
defined('_JEXEC') or die;

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

$jver = new JVersion();

if ($jver->RELEASE == '1.5') {
    if (file_exists(dirname(__FILE__) . '/admin/vendor/autoload.php')) {
        require_once(dirname(__FILE__) . '/admin/vendor/autoload.php');
    }

    if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
        require_once(dirname(__FILE__) . '/vendor/autoload.php');
    }

    if (file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php')) {
        require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
    }

    function updateSettings($name, $settings) {
        $db = JFactory::getDBo();
        $jver = new JVersion();

        $query = "UPDATE ";
        $query .= $db->nameQuote('#__components');
        $query .= " SET " . $db->nameQuote('params') . " = " . $db->quote(json_encode(array($name => $settings)));
        $query .= " WHERE " . $db->nameQuote('option') . " = " . $db->quote('com_bidorbuystoreintegrator');

        $db->setQuery($query);
        $db->query();
    }

    function getSettings() {
        $bobsiParams = json_decode((JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw))
            ? get_object_vars(json_decode(JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw))
            : array();
        return isset($bobsiParams[bobsi\Settings::name]) ? $bobsiParams[bobsi\Settings::name] : array();
    }

    $bidorbuyStoreIntegrator = new bobsi\Core();
    $settings = getSettings();
    if (!empty($settings)) {
        updateSettings(bobsi\Settings::name, $bidorbuyStoreIntegrator->getSettings()->serialize(true));
    }
}