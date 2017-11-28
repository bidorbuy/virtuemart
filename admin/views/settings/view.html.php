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

// import Joomla view library
jimport('joomla.application.component.view');
$jver = new JVersion();
defined('JVER') OR define('JVER', $jver->RELEASE);

class BidorbuyStoreIntegratorViewSettings extends JViewLegacy {

    protected $form;
    protected $params;
    protected $includedCats;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    function display($tpl = NULL) {
        $document = JFactory::getDocument();
        JHtml::_('behavior.framework', true);
        $document->setMetaData('X-UA-Compatible', 'IE=9', TRUE);
        $document->addStyleSheet(JURI::root()
            . 'administrator/components/com_bidorbuystoreintegrator/assets/css/settings.css');
        $document->addScript(JURI::root()
            . 'administrator/components/com_bidorbuystoreintegrator/assets/js/admin_mootools_based.js');

        $this->form = $this->get('Form');

        $this->params = $this->get('Params');

        if (count($errors = $this->get('Errors'))) {
            JFactory::getApplication()->enqueueMessage(implode('<br />', $errors), 'error');
        }

        parent::display($tpl);
        $this->addToolBar();
    }

    protected function addToolBar() {
        JToolBarHelper::title(JText::_(bobsi\Version::$name), 'bidorbuystoreintegrator');

        JToolBarHelper::custom('save', 'publish.png', 'publish-f2.png', 'Save', FALSE);
        JToolBarHelper::divider();
        JToolBarHelper::custom('export', 'archive.png', 'archive-f2.png', 'Export Tradefeed', FALSE);
        JToolBarHelper::custom('download', 'download.png', 'download-f2.png', 'Download Tradefeed', FALSE);
        JToolBarHelper::divider();
        JToolBarHelper::custom('refreshTokens', 'refresh.png', 'refresh-f2.png', 'Reset Tokens', FALSE);
    }
}