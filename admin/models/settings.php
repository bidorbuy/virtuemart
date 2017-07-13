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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Settings Model
 */
class BidorbuyStoreIntegratorModelSettings extends JModelAdmin {
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct($form = null) {
        parent::__construct($form);

        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $this->bidorbuyStoreIntegratorSettings->unserialize(JComponentHelper::getParams('com_bidorbuystoreintegrator')->get(bobsi\Settings::name), true);
    }

    public function getForm($data = array(), $loadData = true) {
        $form = $this->loadForm('com_bidorbuystoreintegrator.settings', 'settings', array('control' => 'jform', 'load_data' => $loadData));

        return empty($form) ? false : $form;
    }

    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_bidorbuystoreintegrator.settings.settings.data', array());
        if (empty($data)) {
//            Fast method
            $data = (array)$this->getParams();
            $data = array_shift($data);

//            Feng Shui method
//            $data = $this->getParams();
//            $data[bobsi\Settings::nameVersion] = bobsi\Version::$version;
//            $data[bobsi\Settings::nameUsername] = $data->getUsername();
//            $data[bobsi\Settings::namePassword] = $data->getPassword();
//            $data[bobsi\Settings::nameCurrency] = $data->getCurrency();
//            $data[bobsi\Settings::nameFilename] = $data->getFilename();
//            $data[bobsi\Settings::nameCompressLibrary] = $data->getCompressLibrary();
//            $data[bobsi\Settings::nameDefaultStockQuantity] = $data->getDefaultStockQuantity();
//            $data[bobsi\Settings::nameExportQuantityMoreThan] = $data->getExportQuantityMoreThan();
//            $data[bobsi\Settings::nameExportStatuses] = $data->getExportStatuses();
//            $data[bobsi\Settings::nameExportVisibilities] = $data->getExportVisibilities();
//            $data[bobsi\Settings::nameExcludeCategories] = $data->getExcludeCategories();
//            $data[bobsi\Settings::nameEnableEmailNotifications] = $data->getEnableEmailNotifications();
//            $data[bobsi\Settings::nameEmailNotificationAddresses] = $data->getEmailNotificationAddresses();
//            $data[bobsi\Settings::nameLoggingLevel] = $data->getLoggingLevel();
        }

        return $data;
    }

    public function getParams() {
        return $this->bidorbuyStoreIntegratorSettings;
    }
}