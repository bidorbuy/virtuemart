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

class BobLoggingLevelField {
    public $type = 'LoggingLevel';
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
        $logging_levels = $this->bidorbuyStoreIntegratorSettings->getLoggingLevelOptions();
        $options = array();

        foreach ($logging_levels as $level) {
            $options[] = JHtml::_('select.option', $level, ucfirst($level));
        }
        return $options;
    }
}