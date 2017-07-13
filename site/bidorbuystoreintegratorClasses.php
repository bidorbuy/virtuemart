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

require_once(dirname(__FILE__) . '/bidorbuystoreintegratorCommon.php');

class BidorbuyStoreIntegratorControllerVM2 extends BidorbuyStoreIntegratorControllerCommon {
    
    protected  $script;
    
    public function __construct($config = array()) {
        require_once(dirname(__FILE__) . '/../../administrator/components/com_virtuemart/helpers/config.php');
        require_once(dirname(__FILE__) . '/../../administrator/components/com_virtuemart/helpers/calculationh.php');
        require_once(JPATH_ADMINISTRATOR . '/components/com_bidorbuystoreintegrator/script.php');

        parent::__construct($config);
        $this->script = new com_bidorbuyStoreIntegratorInstallerScript();
        
        $this->bidorbuyStoreIntegrator =$this->script->coreInitialize(JComponentHelper::getParams('com_bidorbuystoreintegrator')->get(bobsi\Settings::name));

        $this->virtueMartCustomFields = VmModel::getModel('customfields');
        $this->virtueMartProduct = VmModel::getModel('product');
        $this->virtueMartModelMedia = VmModel::getModel('media');
        $this->virtueMartModelCategory = VmModel::getModel('category');
        $this->virtueMartModelShipmentMethod = VmModel::getModel('shipmentmethod');
        $this->calculationHelper = calculationHelper::getInstance();

        foreach ($this->virtueMartModelShipmentMethod->getShipments() as $shipment) {
            if ($shipment->published) {
                $this->shipmentMethods[] = $shipment->shipment_name;
            }
        }
        $this->shipmentMethods = implode(', ', $this->shipmentMethods);
    }

    public function download() {
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        $exportConfiguration = array(
            bobsi\Settings::paramCategories => $this->getExportCategoriesIds($this->bidorbuyStoreIntegrator->getSettings()->getExcludeCategories()),
        );

        $this->bidorbuyStoreIntegrator->download($token, $exportConfiguration);
    }

    public function downloadl() {
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        $this->bidorbuyStoreIntegrator->downloadl($token);
    }

    public function version() {
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        $phpinfo = JFactory::getApplication()->input->get('phpinfo', '', 'STRING');
        $this->bidorbuyStoreIntegrator->showVersion($token, 'y' == $phpinfo);
    }

    public function resetaudit() {
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        if (!$this->bidorbuyStoreIntegrator->canTokenDownload($token)) {
            $this->bidorbuyStoreIntegrator->show403Token($token);
        }

        JFactory::getDbo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getTruncateJobsQuery());
        JFactory::getDbo()->query();

        JFactory::getDbo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getTruncateProductQuery());
        JFactory::getDbo()->query();

        $this->script->addAllProductsInQueue(true);

        $this->bidorbuyStoreIntegrator->resetaudit();
    }

    public function showVersion(){
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        $this->bidorbuyStoreIntegrator->showVersion($token,true);
    }

    public function export() {
        $token = JFactory::getApplication()->input->get(bobsi\Settings::paramToken, '', 'STRING');
        $productsIds = JFactory::getApplication()->input->get(bobsi\Settings::paramIds, '', 'STRING');
        $productStatus = JFactory::getApplication()->input->get(bobsi\Settings::paramProductStatus, bobsi\Queries::STATUS_UPDATE, 'STRING');

        $exportConfiguration = array(
            bobsi\Settings::paramIds => $productsIds,
            bobsi\Settings::paramProductStatus => $productStatus,

            bobsi\Tradefeed::settingsNameExcludedAttributes => array('Width', 'Height', 'Length'),
            bobsi\Settings::paramCallbackGetProducts => array($this, 'getProducts'),
            bobsi\Settings::paramCallbackGetBreadcrumb => array($this, 'getBreadcrumb'),
            bobsi\Settings::paramCallbackExportProducts => array($this, 'exportProducts'),
            bobsi\Settings::paramExtensions => array(),
            bobsi\Settings::paramCategories => $this->getExportCategoriesIds($this->bidorbuyStoreIntegrator->getSettings()->getExcludeCategories()),
        );

        //Get installed components
        $components = JInstaller::getInstance()->discover();
        $extensions = &$exportConfiguration[bobsi\Settings::paramExtensions];
        foreach ($components as $component) {
            if ($component->type == 'component')
                $extensions[$component->name] = $component->name . ' Version: ' . json_decode($component->manifest_cache)->version;
        }

        $this->bidorbuyStoreIntegrator->export($token, $exportConfiguration);
    }

    public function &getProducts(&$exportConfiguration) {
        $itemsPerIteration = intval($exportConfiguration[bobsi\Settings::paramItemsPerIteration]);
        $iteration = intval($exportConfiguration[bobsi\Settings::paramIteration]);
        $categoryId = $exportConfiguration[bobsi\Settings::paramCategoryId];

        //Set (_noLimit = true) to avoid VirtueMart limit: Configuration->Templates "Frontend default items per list view"
        $isLimit = $this->virtueMartProduct->_noLimit;
        $this->virtueMartProduct->_noLimit = true;
        $products = $this->virtueMartProduct->getProductsInCategory($categoryId);
        $this->virtueMartProduct->_noLimit = $isLimit;

        $products_slice = array_slice($products, $itemsPerIteration * $iteration, $itemsPerIteration);

        return $products_slice;
    }

    public function &exportProducts(&$productId, &$exportConfiguration = array()) {
        $exportQuantityMoreThan = $this->bidorbuyStoreIntegrator->getSettings()->getExportQuantityMoreThan();
        $defaultStockQuantity = $this->bidorbuyStoreIntegrator->getSettings()->getDefaultStockQuantity();
//        $exportActiveProducts = $this->bidorbuyStoreIntegrator->getSettings()->getExportActiveProducts() ? array(1) : array(0, 1);
        $exportActiveProducts = array(1);
        $exportProducts = array();

        $product = $this->virtueMartProduct->getProduct($productId);

        if (empty($product->categories)) {
            $product->categories = array('0'); //Uncategorized product
        }

        $categoriesMatching = array_intersect($exportConfiguration[bobsi\Settings::paramCategories], $product->categories);

        if (!in_array($product->published, $exportActiveProducts) OR empty($categoriesMatching)) {
            $this->bidorbuyStoreIntegrator->logInfo('Product does not satisfy published requirements, product id: ' . $product->virtuemart_product_id);
            return $exportProducts;
        }

        if ($this->calcProductQuantity($product, $defaultStockQuantity) > $exportQuantityMoreThan) {

            //Get variations of the current product
            //Fetching all custom fields and sort fields by 'is_cart_attribute'
            $customFields = $this->virtueMartCustomFields->getproductCustomslist($product->virtuemart_product_id);
            $customFieldsCart = array();
            $variations = array();
            if (is_array($customFields) && !empty($customFields)) {
                foreach ($customFields as $customField) {
                    //If the price depends on the field value
                    if ($customField->is_cart_attribute === '1') {
                        $customFieldsCart[] = $customField;
                    }
                }
            }

            if (!empty($customFieldsCart)) {
                $sortVariations = array();
                foreach ($customFieldsCart as $fieldCart) {

                    $sortVariations[$fieldCart->custom_title][] = array(
                        'value' => $fieldCart->custom_value,
                        'price' => (float)$fieldCart->custom_price,
                        'id' => $fieldCart->virtuemart_customfield_id);
                }

                $variations = $this->array_cartesian($sortVariations);
            }

            //If variation available - process it as independent product
            if (empty($variations)) {
                $variations[] = array();
            }

            foreach ($variations as $variation) {
                $tempProduct = $this->buildExportProduct($product, $variation);
                if (intval($tempProduct[bobsi\Tradefeed::nameProductPrice]) <= 0) {
                    $this->bidorbuyStoreIntegrator->logInfo('Product price <= 0, skipping, product id: ' . $product->virtuemart_product_id);
                    continue;
                }

                $categories = array();
                $categoriesIds = array();

                $bidorbuy_settings = (array)$this->bidorbuyStoreIntegrator->getSettings();
                $bidorbuy_settings = array_shift($bidorbuy_settings);

                foreach ($categoriesMatching as $categoryId) {
                    if (isset($bidorbuy_settings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME]) && $bidorbuy_settings[BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME]) {
                        $categories[] = $this->virtueMartModelCategory->getCategory($categoryId, FALSE)->slug;
                        $categoriesIds[] = $categoryId;
                    } else {
                        $categories[] = $this->getBreadcrumb($categoryId);
                        $categoriesIds[] = $categoryId;
                    }
                }
                $tempProduct[bobsi\Settings::paramCategoryId] = bobsi\Tradefeed::categoryIdDelimiter . join(bobsi\Tradefeed::categoryIdDelimiter, $categoriesIds) . bobsi\Tradefeed::categoryIdDelimiter;
                $tempProduct[bobsi\Tradefeed::nameProductCategory] = join(bobsi\Tradefeed::categoryNameDelimiter, $categories);
                $exportProducts[] = $tempProduct;
            }

            $exportProducts[bobsi\Tradefeed::nameProductSummary] = $product->product_s_desc;
            $exportProducts[bobsi\Tradefeed::nameProductDescription] = $product->product_desc;
        } else {
            $this->bidorbuyStoreIntegrator->logInfo('QTY is not enough to export product id: ' . $product->virtuemart_product_id);
        }

        return $exportProducts;
    }

    public function getExportCategoriesIds($ids = array(), $categories = array()) {
        //Set (_noLimit = true) to avoid VirtueMart limit: Configuration->Templates "Frontend default items per list view"
        $isLimit = $this->virtueMartModelCategory->_noLimit;
        $this->virtueMartModelCategory->_noLimit = true;
        $categories = $this->virtueMartModelCategory->getCategories(false);
        $this->virtueMartModelCategory->_noLimit = $isLimit;

        $uncategorized = new stdClass();
        $uncategorized->virtuemart_category_id = 0;
        $uncategorized->category_name = 'Uncategorized';
        $categories[] = $uncategorized;

        return parent::getExportCategoriesIds($ids, $categories);
    }

    private function calcProductQuantity($product, $default = 0) {
        $qty = intval($product->product_in_stock);
        return $qty ? $qty : ((VmConfig::get('stockhandle', 'none') == 'none') ? $default : 0);
    }

    public function getBreadcrumb($categoryId) {
        $categories = $this->virtueMartModelCategory->getParentsList($categoryId);
        $names = array();
        if ($categories) {
            foreach ($categories as $c) {
                $names[] = $c->category_name;
            }
        }
        return implode(' > ', $names);
    }

    private function &buildExportProduct(&$product, $variations = array()) {
        $exportedProduct = array();

        $productCode = parent::getCode($product->virtuemart_product_id, $product->product_sku, $variations);

        $exportedProduct[bobsi\Tradefeed::nameProductId] = $product->virtuemart_product_id;
        $exportedProduct[bobsi\Tradefeed::nameProductName] = $product->product_name;
        $exportedProduct[bobsi\Tradefeed::nameProductCode] = $productCode;

        //Gathering rules affecting the product
        // $taxesAndDiscountsTypes = array('Tax','VatTax','DBTax','DATax','Marge');
        $taxes = array();
        $taxes[] = $this->calculationHelper->gatherEffectingRulesForProductPrice('Tax', $product->product_tax_id);
        $taxes[] = $this->calculationHelper->gatherEffectingRulesForProductPrice('VatTax', $product->product_tax_id);

        $discounts = array();
        $discounts[] = $this->calculationHelper->gatherEffectingRulesForProductPrice('DBTax', $product->product_discount_id);
        $discounts[] = $this->calculationHelper->gatherEffectingRulesForProductPrice('DATax', $product->product_discount_id);
        //It isn't necessary apply marge rules because marge is included to  $product->prices['basePrice']
        //$margeArray[] = $this->calculationHelper->gatherEffectingRulesForProductPrice('Marge', $product->product_marge_id);

        $totalPrice = $product->prices['basePrice'];
        $attrs = array();
        //Define total price of the product variation: prices of all current variations
        $varPrices = 0;
        if (!empty($variations)) {
            foreach ($variations as $titleVariation => $valueAndPrice) {
                $varPrices += $this->applyRules($taxes, $valueAndPrice['price']);
                $attrs[] = array('name' => $titleVariation, 'value' => $valueAndPrice['value']);
            }
            $productCurrency = CurrencyDisplay::getInstance();
            $varPrices = $productCurrency->convertCurrencyTo((int)$product->product_currency, $varPrices, true);
        }

        //Apply the tax rules if exist
        $totalPrice = $this->applyRules($taxes, $totalPrice);
        $priceWithoutReduct = round($totalPrice + $varPrices, 2);

        //Apply the discount rules if exist
        $totalPrice = $this->applyRules($discounts, $totalPrice + $varPrices);
        $priceFinal = round($totalPrice, 2);

        if ($product->override == 1) {
            //This case "Overwrite final"
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $product->product_override_price;
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = $priceWithoutReduct;
        } elseif ($product->override == -1) {
            //This case "Overwrite price to be taxed"
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $this->applyRules($taxes, $product->product_override_price);
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = $priceWithoutReduct;
        } elseif ($priceFinal != $priceWithoutReduct) {
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $priceFinal;
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = $priceWithoutReduct;
        } else {
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $priceFinal;
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = '';
        }

        $exportedProduct[bobsi\Tradefeed::nameProductCondition] = bobsi\Tradefeed::conditionNew;
        $exportedProduct[bobsi\Tradefeed::nameProductShippingClass] = $this->shipmentMethods;

        // string to float
        if ($product->product_height + 0) $attrs[] = array('name' => bobsi\Tradefeed::nameProductAttrHeight, 'value' => number_format($product->product_height + 0, 2, '.', ''));
        if ($product->product_width + 0) $attrs[] = array('name' => bobsi\Tradefeed::nameProductAttrWidth, 'value' => number_format($product->product_width + 0, 2, '.', ''));
        if ($product->product_length + 0) $attrs[] = array('name' => bobsi\Tradefeed::nameProductAttrLength, 'value' => number_format($product->product_length + 0, 2, '.', ''));
        if ($product->product_weight + 0) $attrs[] = array('name' => bobsi\Tradefeed::nameProductAttrShippingWeight, 'value' => number_format($product->product_weight + 0, 2, '.', '') . $product->product_weight_uom);

        //Would be nice though if "Brand"/"Manufacturer" value is appended first.  issue #2180
        if ($product->mf_name) $attrs = array_merge(array(array('name' => 'Brand', 'value' => $product->mf_name)), $attrs);

        $exportedProduct[bobsi\Tradefeed::nameProductAttributes] = $attrs;

        if (isset($product->customfields)) {
            foreach ($product->customfields as $field) {
                $exportedProduct[bobsi\Tradefeed::nameProductAttributes][] = array ('name' => $field->custom_title, 'value' => $field->custom_value);
            }
        }

        $exportedProduct[bobsi\Tradefeed::nameProductAvailableQty] =
            $this->calcProductQuantity($product, $this->bidorbuyStoreIntegrator->getSettings()->getDefaultStockQuantity());

        $images = $this->getImageURL($product);
        $exportedProduct[bobsi\Tradefeed::nameProductImageURL] = isset($images[0]) ? $images[0] : null;
        $exportedProduct[bobsi\Tradefeed::nameProductImages] = $images;

        //Add to array base url
        $exportedProduct[bobsi\Tradefeed::nameBaseUrl] = trim(JURI::base(),'/');
        return $exportedProduct;
    }

    public function applyRules($rules, $price) {
        foreach ($rules as $rule) {
            if (!empty($rule)) {
                $price = $this->calculationHelper->executeCalculation($rule, $price);
            }
        }
        return $price;
    }

    public function getImageURL($product = null) {
        $parentProductImages = array();
        if ($product->product_parent_id != '0') {
            $parent = $this->virtueMartProduct->getProduct($product->product_parent_id, false);
            if (!empty($parent->virtuemart_media_id)) {
                $parentProductImages = $parent->virtuemart_media_id;
            }
        }
        //If the product has several images the primary image should be set. primaryImageId is the 1-st element of the array $product->virtuemart_media_id
        $primaryImageIds = count($product->virtuemart_media_id) ? $product->virtuemart_media_id : (!count($parentProductImages) ? $parentProductImages : array());
        //It builds array of VmImage objects.
        $images = $this->virtueMartModelMedia->createMediaByIds($primaryImageIds);

        $images_urls = array();
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image->published) {
                    $images_urls[] = (JURI::base() . '/' . $image->file_url);
                }
            }
        }
        return $images_urls;
    }

}