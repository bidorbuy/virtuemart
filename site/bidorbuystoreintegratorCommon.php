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

class BidorbuyStoreIntegratorControllerCommon extends JControllerLegacy {

    protected $bidorbuyStoreIntegrator = null;

    protected $virtueMartModelMedia = null;
    protected $virtueMartProduct = null;
    protected $virtueMartModelCategory = null;
    protected $virtueMartModelShipmentMethod = null;
    protected $shipmentMethods = array(); // its same for all products
    protected $calculationHelper = null;
    public $maxConnections;

    public function __construct($config = array()){
        parent::__construct($config);
        $this->bidorbuyStoreIntegrator = new bobsi\Core();
    }

    /**
     * Function returns string with value for ProductCode
     *
     * @param $productID
     * @param string $sku
     * @param $variations
     * @return string ProductCode value
     */
    public function getCode($productID, $sku = '', $variations = array()) {
        $code = $productID;
        if (!empty($variations)) {
            foreach ($variations as $variation) {
                $code .= '-' . $variation['id'];
            }
        }
        $code .= !empty($sku) ? '-' . $sku : '';
        return $code;
    }

    public function getExportCategoriesIds($ids = array(), $categories = array()) {
        $ids2 = array();
        foreach ($categories as $c) {
            $ids2[] = isset($c->virtuemart_category_id) ? $c->virtuemart_category_id : $c['category_child_id'];
        }
        return array_values(array_diff($ids2, $ids));
    }

    protected function array_cartesian($input) {
        $result = array();

        while (list($key, $values) = each($input)) {
            // If a sub-array is empty, it doesn't affect the cartesian product
            if (empty($values)) {
                continue;
            }

            // Special case: seeding the product array with the values from the first sub-array
            if (empty($result)) {
                foreach ($values as $value) {
                    $result[] = array($key => $value);
                }
            } else {
                // Second and subsequent input sub-arrays work like this:
                //   1. In each existing array inside $product, add an item with
                //      key == $key and value == first item in input sub-array
                //   2. Then, for each remaining item in current input sub-array,
                //      add a copy of each existing array inside $product with
                //      key == $key and value == first item in current input sub-array

                // Store all items to be added to $product here; adding them on the spot
                // inside the foreach will result in an infinite loop
                $append = array();
                foreach ($result as &$product) {
                    // Do step 1 above. array_shift is not the most efficient, but it
                    // allows us to iterate over the rest of the items with a simple
                    // foreach, making the code short and familiar.
                    $product[$key] = array_shift($values);

                    // $product is by reference (that's why the key we added above
                    // will appear in the end result), so make a copy of it here
                    $copy = $product;

                    // Do step 2 above.
                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    // Undo the side effecst of array_shift
                    array_unshift($values, $product[$key]);
                }

                // Out of the foreach, we can add to $results now
                $result = array_merge($result, $append);
            }
        }

        return $result;
    }
}