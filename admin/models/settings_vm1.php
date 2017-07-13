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
defined('_JEXEC') or die('Restricted access');

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . '/elements/bobcategories_vm1.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . '/elements/compresslibrary_vm1.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . '/elements/logginglevel_vm1.php');

class BidorbuyStoreIntegratorModelSettings_Vm1 extends JModel {
    public static $formData = array();
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct($formDefault = null) {
        parent::__construct();

        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $bobsiParams = json_decode((JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) ?
            get_object_vars(json_decode(JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) :
            array();

        if (isset($bobsiParams[bobsi\Settings::name])) {
            $this->bidorbuyStoreIntegratorSettings->unserialize($bobsiParams[bobsi\Settings::name], true);
        }

        self::$formData = $this->loadFormData();
    }

    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_bidorbuystoreintegrator.settings.settings.data', array());
        if (empty($data)) {
            $data = (array)$this->getParams();
            $data = array_shift($data);
        }
        return $data;
    }

    public function getParams() {
        return $this->bidorbuyStoreIntegratorSettings;
    }

}


class BidorbuyStoreIntegratorForm {
    public $formData = array();
    var $wordings = array();
    private $bidorbuyStoreIntegratorSettings = null;
    private $bidorbuyStoreIntegrator = null;

    private $formField = null;

    public function __construct() {
        $this->formField = new BidorbuyStoreIntegratorField();
        $this->bidorbuyStoreIntegrator = new bobsi\Core();
        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();

        $bobsiParams = json_decode((JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) ?
            get_object_vars(json_decode(JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) :
            array();

        if (isset($bobsiParams[bobsi\Settings::name])) {
            $this->bidorbuyStoreIntegratorSettings->unserialize($bobsiParams[bobsi\Settings::name], true);
        }

        $this->formData = $this->loadFormData();
        $this->wordings = $this->bidorbuyStoreIntegrator->getSettings()->getDefaultWordings();
    }

    /*************************************************************************************/
    public function getFieldset($string = '') {
        $fieldSet = array();

        switch ($string) {
            case 'COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CONFIGS':
                $fieldSet = array('UserName', 'Password', 'FileName', 'CompressLibrary',
                    'Email', 'CheckboxNotification', 'LoggingLevel');
                break;
            case 'COM_BIDORBUYSTOREINTEGRATOR_CONFIG_EXPORT_CRITERIA':
                $fieldSet = array('ExportQuantityMoreThan', 'Categories');
                break;
            case '':
                break;
        }

        $fields = array();
        foreach ($fieldSet as $field) {
            $fields[] = call_user_func(array($this, 'get' . $field . 'Field'));
        }

        return $fields;
    }

    /***************************************************************************************/

    public function getUserNameField() {
        $name = bobsi\Settings::nameUsername;
        return $this->formField->getField($this->createInputArray($name), $this->createLabelArray($name));
    }

    public function getPasswordField() {
        $name = bobsi\Settings::namePassword;
        $label = $this->createLabelArray($name);
        $input = array(
            'input' => array('type' => 'password', 'name' => 'jform[password]', 'id' => 'jform_password',
                'value' => $this->formData[$name]
            ));

        return $this->formField->getField($input, $label);
    }

    public function getFileNameField() {
        $name = bobsi\Settings::nameFilename;
        $label = $this->createLabelArray($name);
        $input = array(
            'input' => array('type' => 'text', 'name' => 'jform[filename]', 'id' => 'jform_filename',
                'value' => $this->formData[bobsi\Settings::nameFilename]));
        return $this->formField->getField($input, $label);
    }

    public function getCompressLibraryField() {
        $field = new BidorbuyStoreIntegratorField();
        $bobCompressLib = new BobCompressLibraryField();
        $name = bobsi\Settings::nameCompressLibrary;
        $label = $this->createLabelArray($name);
        $options = array();
        foreach ($bobCompressLib->getOptions() as $option) {
            $options[] = array(
                'option' => array('value' => $option->value),
                'childNode' => $option->text);
            if ($this->bidorbuyStoreIntegratorSettings->getCompressLibrary() == $option->value) {
                $options[count($options) - 1]['option']['selected'] = 'selected';
            }
        }
        $input = array(
            'select' => array('name' => 'jform[' . $name . ']', 'id' => 'jform_' . $name),
            'childNode' => $options);
        return $field->getField($input, $label);
    }

    public function getEmailField() {
        $name = bobsi\Settings::nameEmailNotificationAddresses;
        $label = $this->createLabelArray($name);
        $input = array(
            'input' => array('type' => 'text', 'name' => 'jform[' . bobsi\Settings::nameEmailNotificationAddresses . ']', 'id' => 'jform_enableNotificationAddresses',
                'value' => $this->formData[bobsi\Settings::nameEmailNotificationAddresses]));
        return $this->formField->getField($input, $label);
    }

    public function getCheckboxNotificationField() {
        $name = bobsi\Settings::nameEnableEmailNotifications;
        $label = $this->createLabelArray($name);
        $input = array('input' => array('type' => 'checkbox', 'name' => 'jform[' . $name . ']', 'id' => 'jform_' . $name));
        ($this->formData[$name]) && $input['input']['checked'] = 'checked';

        return $this->formField->getField($input, $label);
    }

    public function getLoggingLevelField() {
        $name = bobsi\Settings::nameLoggingLevel;
        $field = new BidorbuyStoreIntegratorField();
        $bobLoggingLevel = new BobLoggingLevelField();

        $label = $this->createLabelArray($name);
        $options = array();
        foreach ($bobLoggingLevel->getOptions() as $option) {
            $options[] = array(
                'option' => array('value' => $option->value),
                'childNode' => $option->text);
            if ($this->bidorbuyStoreIntegratorSettings->getLoggingLevel() == $option->value) {
                $options[count($options) - 1]['option']['selected'] = 'selected';
            }
        }
        $input = array(
            'select' => array('name' => 'jform[' . $name . ']', 'id' => 'jform_' . $name),
            'childNode' => $options);
        return $field->getField($input, $label);
    }

    /*
     * Functions for "Export criteria" fieldset
     */

    public function getExportQuantityMoreThanField() {
        $name = bobsi\Settings::nameExportQuantityMoreThan;
        $label = $this->createLabelArray($name);
        $input = array(
            'input' => array('type' => 'text', 'name' => 'jform[' . $name . ']', 'id' => 'jform_' . $name,
                'value' => $this->formData[$name]));
        return $this->formField->getField($input, $label);
    }


    public function getCategoriesField() {
        $field = new BidorbuyStoreIntegratorField();
        $bobCategories = new BobCategoriesField();
        $field->input = $bobCategories->getBobsiCategories();
        $field->name = 'jform[' . bobsi\Settings::nameExcludeCategories . '][]';
        return $field;
    }


    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_bidorbuystoreintegrator.settings.settings.data', array());
        if (empty($data)) {
            $data = (array)$this->getParams();
            $data = array_shift($data);
        }
        return $data;
    }

    public function getParams() {
        return $this->bidorbuyStoreIntegratorSettings;
    }

    private function createLabelArray($name, $tip = true) {
        $label = array('label' => array('id' => 'jform_' . $name . '-lbl', 'for' => 'jform_' . $name),
            'childNode' => $this->wordings[$name][bobsi\Settings::nameWordingsTitle]);
        if ($tip) {
            $label['label']['class'] = 'hasTip';
            $label['label']['title'] = $this->wordings[$name][bobsi\Settings::nameWordingsTitle] .
                '::' . htmlspecialchars($this->wordings[$name][bobsi\Settings::nameWordingsDescription]);
        }

        return $label;
    }

    private function createInputArray($name, $type = 'text') {
        $input = array('input' => array(
            'type' => $type,
            'name' => 'jform[' . $name . ']',
            'id' => 'jform_' . $name,
            'value' => $this->formData[$name]
        ));
        return $input;
    }
}


class BidorbuyStoreIntegratorField {
    public $label;
    public $input;
    public $name;
    private $tag = '';

    public function getField($input, $label = array()) {
        $field = new self;
        $field->input = $input ? $field->createHtmlNode($input) : '';
        $field->label = $label ? $field->createHtmlNode($label) : '';

        return $field;
    }

    private function createHtmlNode($nodes = array(), $is_tag_name = true, &$html_string = '', &$tags = array(), $iterationIndex = 0) {
        if ($is_tag_name) {
            $keys = array_keys($nodes);
            $this->tag = $keys[0];
            $tags[] = $keys[0];
            $html_string .= '<' . $this->tag . ' ';
        }

        foreach ($nodes as $node => $attr) {
            if ($node == 'childNode') {

                if (is_array($attr)) {
                    foreach ($attr as $child) {
                        $this->createHtmlNode($child, true, $html_string, $tags, ++$iterationIndex);
                    }
                    $html_string .= '</' . $tags[0] . '>';
                } else {
                    $html_string .= $attr;
                    $html_string .= '</' . $tags[$iterationIndex] . '>';
                }
//                    $html_string .= '</' . $this->tags[0] . '>';
            } elseif (is_array($attr)) {
                $this->createHtmlNode($attr, false, $html_string, $tags);
            } elseif (next($nodes) === false) {
                $html_string .= ' ' . $node . '="' . $attr . '"';
                $html_string .= '>';
            } else {
                $html_string .= ' ' . $node . '="' . $attr . '"';
            }
        }
        return $html_string;
    }
}
