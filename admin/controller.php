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

// import Joomla controller library
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

// workaround to load JController
class_exists('JController');

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/script.php');
//For VirtueMart version 2.x, version 1.x not support.
require_once(dirname(__FILE__) . '/../com_virtuemart/helpers/config.php');
require_once(dirname(__FILE__) . '/../com_virtuemart/models/category.php');
require_once(dirname(__FILE__) . '/../com_virtuemart/helpers/shopfunctions.php');


$jver = new JVersion();
defined('JVER') OR define('JVER', $jver->RELEASE);

class BidorbuyStoreIntegratorController extends JController {
    private $bidorbuyStoreIntegrator;

    function __construct($config = array()) {
        $this->bidorbuyStoreIntegrator = new bobsi\Core();

        $bobsiParams[bobsi\Settings::name] = JComponentHelper::getParams('com_bidorbuystoreintegrator')->get(bobsi\Settings::name);
        
        if (isset($bobsiParams[bobsi\Settings::name])) {
            $this->bidorbuyStoreIntegrator->getSettings()->unserialize($bobsiParams[bobsi\Settings::name], true);
        }

        $app = JFactory::getApplication();
        $warnings = $this->bidorbuyStoreIntegrator->getWarnings();
        foreach ($warnings as $warning) {
            $app->enqueueMessage($warning, 'warning');
        }

        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = false) {
        $app = JFactory::getApplication();

        // set default view if not set
            $input = $app->input;
            $input->set('view', $input->getCmd('view', 'settings'));
            $bobsiLoggingFormAction = $input->getString(bobsi\Settings::nameLoggingFormAction, '');
            $bobsiLoggingFormFileName = $input->getString(bobsi\Settings::nameLoggingFormFilename, '');

        if ($bobsiLoggingFormAction) {
            $messages = $this->bidorbuyStoreIntegrator->processAction(
                $bobsiLoggingFormAction, array(bobsi\Settings::nameLoggingFormFilename => $bobsiLoggingFormFileName));
            foreach ($messages as $message) {
                $app->enqueueMessage($message, 'message');
            }
        }

        //parent display() function copied to pass bidorbuyStoreIntegrator to the view
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $viewLayout = JRequest::getCmd('layout', 'default');

        //Use JPATH_COMPONENT instead of $this->basePath (the field doesn't exist in Joomla 1.5).
        $basePath = JPATH_COMPONENT;
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $basePath, 'layout' => $viewLayout));
        $view->bidorbuyStoreIntegrator = $this->bidorbuyStoreIntegrator;

        // Get/Create the model
        //if ($model = $this->getModel($viewName)) {
        //TODO: This shouldn't be hardcoded !
        if ($model = $this->getModel($viewName)) {
            $view->setModel($model, true);
        }
        $view->assignRef('document', $document);
        $conf = JFactory::getConfig();
        // Display the view
        if ($cachable && $viewType != 'feed' && $conf->get('caching') >= 1) {
            $option = JRequest::getCmd('option');
            $cache = JFactory::getCache($option, 'view');
            if (is_array($urlparams)) {
//                $app = JFactory::getApplication();
                if (!empty($app->registeredurlparams)) {
                    $registeredurlparams = $app->registeredurlparams;
                } else {
                    $registeredurlparams = new stdClass;
                }
                foreach ($urlparams as $key => $value) {
                    $registeredurlparams->$key = $value;
                }
                $app->registeredurlparams = $registeredurlparams;
            }
            $cache->get($view, 'display');
        } else {
            $view->display();
        }

        return $this;
    }

    public function save($key = null, $urlVar = null) {
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $app = JFactory::getApplication();

        $data = $app->input->get('jform', array(), 'ARRAY');

        $wordings = $this->bidorbuyStoreIntegrator->getSettings()->getDefaultWordings();
        $presaved_settings = array();
        $prevent_saving = false;

        $settings_checklist = array(
            bobsi\Settings::nameUsername => 'strval',
            bobsi\Settings::namePassword => 'strval',
            bobsi\Settings::nameFilename => 'strval',
            bobsi\Settings::nameCompressLibrary => 'strval',
            bobsi\Settings::nameDefaultStockQuantity => 'intval',
            bobsi\Settings::nameEmailNotificationAddresses => 'strval',
            bobsi\Settings::nameEnableEmailNotifications => 'bool',
            bobsi\Settings::nameLoggingLevel => 'strval',
            bobsi\Settings::nameExportQuantityMoreThan => 'intval',
            bobsi\Settings::nameExcludeCategories => 'categories',
            BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME => 'bool',
//          bobsi\Settings::nameExportActiveProducts => 'bool'
        );

        foreach ($settings_checklist as $setting => $prevalidation) {
            switch ($prevalidation) {
                case ('strval'):
                    $presaved_settings[$setting] = isset($data[$setting]) ? strval($data[$setting]) : '';
                    break;
                case ('intval'):
                    $presaved_settings[$setting] = isset($data[$setting]) ? $data[$setting] : 0;
                    break;
                case ('bool'):
                    $presaved_settings[$setting] = isset($data[$setting]) ? (bool)($data[$setting]) : FALSE;
                    break;
                case ('categories'):
                    $presaved_settings[$setting] = isset($data[$setting]) ? (array)$data[$setting] : array();
            }

            //See #3268
            if (BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME === $setting) {
                continue;
            }

            if (!call_user_func($wordings[$setting][bobsi\Settings::nameWordingsValidator], $presaved_settings[$setting])) {
                $app->enqueueMessage('Invalid value: ' . $wordings[$setting][bobsi\Settings::nameWordingsTitle], 'error');
                $prevent_saving = true;
            }
        }

        if (!$prevent_saving) {
            //Saving tokens
            $presaved_settings[bobsi\Settings::nameTokenExport] = $this->bidorbuyStoreIntegrator->getSettings()->getTokenExport();
            $presaved_settings[bobsi\Settings::nameTokenDownload] = $this->bidorbuyStoreIntegrator->getSettings()->getTokenDownload();

            //see #3628. We added new export criteria: it should affect resetting settings after pressing Save button
            $reset_audit = FALSE;
            if (isset($presaved_settings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME])) {
                $previousSettings = (array) $this->bidorbuyStoreIntegrator->getSettings();
                $previousSettings = array_shift($previousSettings);
                if (!isset($previousSettings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME]) || $previousSettings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME] !== $presaved_settings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME]) {
                    $reset_audit = TRUE;
                }
            }

            $previousSettingsSerialized = $this->bidorbuyStoreIntegrator->getSettings()->serialize(true);

            $this->bidorbuyStoreIntegrator->getSettings()->unserialize(serialize($presaved_settings));

            $newSettingsSerialized = $this->bidorbuyStoreIntegrator->getSettings()->serialize(true);

            com_bidorbuyStoreIntegratorInstallerScript::updateSettings(bobsi\Settings::name, $newSettingsSerialized);

            if (defined('JVER') && JVER != '1.5') {
                if ($this->bidorbuyStoreIntegrator->checkIfExportCriteriaSettingsChanged($previousSettingsSerialized, $newSettingsSerialized, true) || $reset_audit) {
                    require_once(JPATH_ADMINISTRATOR . '/components/com_bidorbuystoreintegrator/script.php');
                    $x = new com_bidorbuyStoreIntegratorInstallerScript();
                    JFactory::getDbo()->setQuery($x->getBidorbuyStoreIntegrator()->getQueries()->getTruncateJobsQuery());
                    JFactory::getDbo()->query();
                    $x->addAllProductsInQueue(true);
                }

                $this->clearCache();
            }
        }

        $this->setRedirect(JRoute::_('index.php?option=com_bidorbuystoreintegrator', false));
    }

    public function export() {
        $this->setRedirect(JRoute::_(JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=export&t=' . $this->bidorbuyStoreIntegrator->getSettings()->getTokenExport(), false));
    }

    public function download() {
        $this->setRedirect(JRoute::_(JURI::root() . 'index.php?option=com_bidorbuystoreintegrator&task=download&t=' . $this->bidorbuyStoreIntegrator->getSettings()->getTokenDownload(), false));
    }

    public function refreshTokens() {
        $this->bidorbuyStoreIntegrator->processAction(bobsi\Settings::nameActionReset);
        com_bidorbuyStoreIntegratorInstallerScript::updateSettings(bobsi\Settings::name, $this->bidorbuyStoreIntegrator->getSettings()->serialize(true));
        $this->setRedirect(JRoute::_('index.php?option=com_bidorbuystoreintegrator', false));
    }

    private function clearCache() {
        $conf = JFactory::getConfig();

        $options = array(
            'defaultgroup' => '_system',
            'cachebase' => $conf->get('cache_path', JPATH_SITE . '/cache')
        );

        $cache = JCache::getInstance('callback', $options);
        $cache->clean();
    }

}
