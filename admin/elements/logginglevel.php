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

defined('_JEXEC') or die;

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

JFormHelper::loadFieldClass('list');

class JFormFieldLoggingLevel extends JFormFieldList {
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'LoggingLevel';
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct($form = null) {
        parent::__construct($form);

        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $this->bidorbuyStoreIntegratorSettings->unserialize(JComponentHelper::getParams('com_bidorbuystoreintegrator')->get(bobsi\Settings::name), true);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
    protected function getOptions() {
        $logging_levels = $this->bidorbuyStoreIntegratorSettings->getLoggingLevelOptions();
        $options = array();

        foreach ($logging_levels as $level) {
            $options[] = JHtml::_('select.option', $level, ucfirst($level));
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}