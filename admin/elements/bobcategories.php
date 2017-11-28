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

class JFormFieldBobCategories extends JFormFieldList {
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'BobCategories';
    private $bidorbuyStoreIntegratorSettings = null;

    public function __construct($form = null) {
        parent::__construct($form);

        $this->bidorbuyStoreIntegratorSettings = new bobsi\Settings();
        $this->bidorbuyStoreIntegratorSettings->unserialize(JComponentHelper::getParams('com_bidorbuystoreintegrator')->get(bobsi\Settings::name), true);
    }

    /**
     * Form Field class for the Joomla Platform.
     * Supports a generic list of options.
     *
     * @package     Joomla.Platform
     * @subpackage  Form
     * @since       11.1
     */
    protected function getInput() {
        $html = array();

        $categoryModel = VmModel::getModel('category');
        //Getting unlimited count of categories (20 by default in VirtueMart)
        $isLimit = $categoryModel->_noLimit;
        $categoryModel->_noLimit = true;

        $cats = $categoryModel->getCategories(false);
        $uncategorized = new stdClass();
        $uncategorized->virtuemart_category_id = 0;
        $uncategorized->category_name = 'Uncategorized';
        $cats[] = $uncategorized;

        $categoryModel->_noLimit = $isLimit;

        $export_categories = $this->bidorbuyStoreIntegratorSettings->getExcludeCategories();

        $included_categories = '<select id="bobsi-inc-categories" class="bobsi-categories-select" name="bobsi_inc_categories[]" multiple="multiple" size="9">';
        $excluded_categories = '<select id="bobsi-exc-categories" class="bobsi-categories-select" name="' . $this->name . '" multiple="multiple" size="9">';

        foreach ($cats as $category) {
            $t = '<option  value="' . $category->virtuemart_category_id . '">' . $category->category_name . '</option>';
            if (in_array($category->virtuemart_category_id, $export_categories)) {
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
        $html[] = '<td style="text-align:center">
                      <div class="buttons_criteria">
                        <p class="submit"><button name="include" id="include" class="button" type="button">< Include</button></p>
                        <p class="submit"><button name="exclude" id="exclude" class="button" type="button">> Exclude</button></p>
                      </div>  
                   </td>';
        $html[] = '<td>' . $excluded_categories . '</td></tr></table>';

        return implode($html);
    }
}