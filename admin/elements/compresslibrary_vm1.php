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

defined('_JEXEC') or die;

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

class BobCompressLibraryField {

    public $type = 'CompressLibrary';
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct() {
        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $bobsiParams = json_decode((JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) ?
            get_object_vars(json_decode(JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) :
            array();

        if (isset($bobsiParams[bobsi\Settings::name])) {
            $this->bidorbuyStoreIntegratorSettings->unserialize($bobsiParams[bobsi\Settings::name], true);
        }
    }

    public function getOptions() {
        $libs = $this->bidorbuyStoreIntegratorSettings->getCompressLibraryOptions();
        $options = array();

        foreach ($libs as $lib => $info) {
            $options[] = JHtml::_('select.option', $lib, $lib);
        }
        return $options;
    }
}