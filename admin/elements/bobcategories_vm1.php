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

class BobCategoriesField {
    protected $type = 'BobCategories';
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct() {
        require_once(dirname(__FILE__) . '/../../../../components/com_virtuemart/virtuemart_parser.php');
        require_once(dirname(__FILE__) . '/../../com_virtuemart/classes/vmAbstractObject.class.php');
        require_once(dirname(__FILE__) . '/../../com_virtuemart/classes/ps_product_category.php');

        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $bobsiParams = json_decode((JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) ?
            get_object_vars(json_decode(JComponentHelper::getParams('com_bidorbuystoreintegrator')->_raw)) :
            array();
        $this->bidorbuyStoreIntegratorSettings->unserialize($bobsiParams[bobsi\Settings::name], true);
    }

    function getBobsiCategories() {
        $cats = ps_product_category::getCategoryTreeArray();
        $export_categories = $this->bidorbuyStoreIntegratorSettings->getExcludeCategories();
        return BobCategoriesField::getInput($cats, $export_categories);
    }

    function getInput($cats = array(), $export_categories = array()) {
        $included_categories = '<select id="bobsi-inc-categories" class="bobsi-categories-select" name="bobsi_inc_categories[]" multiple="multiple" size="9">';
        $excluded_categories = '<select id="bobsi-exc-categories" class="bobsi-categories-select" name=" ' . 'jform[' . bobsi\Settings::nameExcludeCategories . '][]"' . ' multiple="multiple" size="9">';

        $cats[] = array('category_child_id' => 0, 'category_name' => 'Uncategorized');

        foreach ($cats as $category) {
            $id = $category['category_child_id'];
            $t = '<option  value="' . $id . '">' . $category['category_name'] . '</option>';
            if (in_array($id, $export_categories)) {
                $excluded_categories .= $t;
            } else {
                $included_categories .= $t;
            }
        }
        $included_categories .= '</select>';
        $excluded_categories .= '</select>';


        $html[] = '<table><tr><td><label for="bobsi-inc-categories">Included Categories</label></td>
                    <td></td><td><label for="bobsi-exc-categories">Excluded Categories</label></td></tr>';
        $html[] = '<tr><td>' . $included_categories . '</td>';
        $html[] = '<td>
                    <p class="submit"><button name="include" id="include" class="button" type="button">< Include</button></p>
                    <p class="submit"><button name="exclude" id="exclude" class="button" type="button">> Exclude</button></p>
                   </td>';
        $html[] = '<td>' . $excluded_categories . '</td></tr></table>';

        return implode($html);
    }
}