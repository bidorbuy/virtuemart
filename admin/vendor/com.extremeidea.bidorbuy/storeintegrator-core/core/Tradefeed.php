<?php

/**
 * Copyright (c) 2014, 2015, 2016 Bidorbuy http://www.bidorbuy.co.za
 * This software is the proprietary information of Bidorbuy.
 *
 * All Rights Reserved.
 * Modification, redistribution and use in source and binary forms, with or without
 * modification are not permitted without prior written approval by the copyright
 * holder.
 *
 * Vendor: EXTREME IDEA LLC http://www.extreme-idea.com
 */

namespace com\extremeidea\bidorbuy\storeintegrator\core;

if (!defined('BOB_TRADEFEED_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        // Windows
        case 'WIN':
            define('BOB_TRADEFEED_EOL', "\r\n");
            break;

        // Mac
        case 'DAR':
            define('BOB_TRADEFEED_EOL', "\r");
            break;

        // Unix
        default:
            define('BOB_TRADEFEED_EOL', "\n");
    }
}

if (class_exists('\com\extremeidea\bidorbuy\storeintegrator\core\Tradefeed', FALSE)) {
    return;
}

/**
 * @SuppressWarnings(PHPMD.ConstantNamingConventions)
 */
class Tradefeed {
    //@codingStandardsIgnoreStart
    const xmlVersion = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>';

    const nameRoot = 'ROOT';
    const nameVersion = 'Version';
    const nameUserId = 'UserId';
    const namePluginVersion = 'PluginVersion';
    const nameSchemaVersion = 'SchemaVersion';
    const nameExportCreated = 'ExportCreated';
    const nameProducts = 'Products';

    const nameProduct = 'Product';
    const nameProductId = 'ID';
    const nameProductName = 'ProductName';
    const nameProductCode = 'ProductCode';
    const nameProductCategory = 'Category';
    const nameProductPrice = 'Price';
    const nameProductMarketPrice = 'MarketPrice';
    const nameProductAvailableQty = 'AvailableQty';
    const nameProductCondition = 'Condition';
    const nameProductAttributes = 'ProductAttributes';
    const nameProductShippingClass = 'ShippingProductClass';
    const nameProductImages = 'Images';
    const nameProductImageURL = 'ImageURL';
    const nameProductSummary = 'ProductSummary';
    const nameProductDescription = 'ProductDescription';

    const nameBaseUrl = 'BaseUrl';

    const nameProductAttrWidth = 'Width';
    const nameProductAttrHeight = 'Height';
    const nameProductAttrLength = 'Length';
    const nameProductAttrDepth = 'Depth';
    const nameProductAttrWeight = 'Weight';
    const nameProductAttrShippingWeight = 'ShippingWeight';

    const conditionNew = 0;
    const conditionSecondhand = 1;
    const conditionRefurbished = 2;

    const categoryNameDelimiter = "|";
    const categoryIdDelimiter = "-";

    const settingsNameExcludedAttributes = 'nameExcludedAttributes';
    const nameProductExcludedAttributes = 'nameProductExcludedAttributes';
    const settingsNameAttributesOrder = 'nameAttributesOrder';
    //@codingStandardsIgnoreEnd
    private static $versionInstance;

    public static function createStartRootTag() {
        return self::xmlVersion . BOB_TRADEFEED_EOL . self::tag(self::nameRoot, TRUE);
    }

    public static function createEndRootTag() {
        return self::tag(self::nameRoot, FALSE);
    }

    public static function createStartProductsTag() {
        return self::tag(self::nameProducts, TRUE, 1);
    }

    public static function createEndProductsTag() {
        return self::tag(self::nameProducts, FALSE, 1);
    }

    public static function createVersionSection() {
        $output = self::section(self::nameUserId, 1, FALSE, 2);
        $output .= self::section(self::namePluginVersion, self::getLivePluginVersion(), TRUE, 2);
        $output .= self::section(self::nameSchemaVersion, '1.1', FALSE, 2);
        $output .= self::section(self::nameExportCreated, date('c'), FALSE, 2);

        return self::section(self::nameVersion, $output, FALSE, 1);
    }

    public static function createProductSection(&$data, &$settings = array()) {
        if (is_array($data)) {
            $data = self::prepareProductArray($data, $settings);

            return self::buildXmlViewProduct($data);
        }
    }

    public static function section($tag, $value, $cdata = 1, $tabCount = 0, $forceNewLine = 0) {
        $section = !is_null($value) ? trim($value) : '';

        $tab = '';
        for ($i = 1; $i <= $tabCount; $i++) {
            $tab .= "\t";
        }

        return (strlen($section) == 0
            ? ''
            : ($tab . '<' . $tag . '>' . ($cdata
                    ? self::cdata($section)
                    : (substr($section, 0, 1) == '<'
                    || $forceNewLine ? BOB_TRADEFEED_EOL . $value . $tab : $value)) . '</' . $tag . '>')
            . BOB_TRADEFEED_EOL);
    }

    private static function tag($tag, $open = 1, $tabCount = 0) {
        $tab = '';
        for ($i = 1; $i <= $tabCount; $i++) {
            $tab .= "\t";
        }

        return $tab . '<' . ($open ? '' : '/') . $tag . '>' . BOB_TRADEFEED_EOL;
    }

    private static function cdata($value) {
        return is_numeric($value) ? $value : '<![CDATA[' . $value . ']]>';
    }

    public static function sanitize($value) {
        $value = preg_replace('/[^a-zA-Z0-9]/', '', $value);

        while (!empty($value) AND !ctype_alpha(substr($value, 0, 1))) {
            $value = substr($value, 1, strlen($value));
        }

        return ucfirst($value);
    }

    /**
     * Prepare product data
     *
     * @param array $data array with data from storeintegrator platform
     * @param array $settings array
     *
     * @return mixed
     */
    public static function prepareProductArray(&$data, &$settings = array()) {

        $defaults = array(self::nameProductCategory => NULL, self::nameProductPrice => NULL,
            self::nameProductMarketPrice => NULL, self::nameProductAvailableQty => NULL,
            self::nameProductImages => array(),);

        $data = array_merge($defaults, $data);

        $nameExcludedAttributes =
            isset($settings[self::settingsNameExcludedAttributes]) ? $settings[self::settingsNameExcludedAttributes]
                : array();
        $items = isset($settings[self::settingsNameAttributesOrder])
            ? array_values($settings[self::settingsNameAttributesOrder]) : array();

        $nameAttributesOrder = array_fill_keys($items, '');

        // Dynamic product title and custom attributes
        $attributes = '';

        if (isset($data[self::nameProductAttributes]) && !empty($data[self::nameProductAttributes])) {
            foreach ($data[self::nameProductAttributes] as $name => $value) {
                // Some platforms allows to set many attributes with an identical name
                // Each one is coming inside a separate array to avoid the problem
                if (is_array($value)) {
                    if (!isset($value['name']) || !isset($value['value'])) {
                        // Defect #3718 Notice: Undefined index:
                        continue;
                    }
                }

                $label = self::sanitize(self::getAttrName($name, $value));
                if (strlen($label) > 0) {
                    $nameAttributesOrder[] = array($label => ucfirst(self::getAttrValue($value)));
                }
            }
            $data[self::nameProductExcludedAttributes] = isset($data[self::nameProductExcludedAttributes])  ?
                $data[self::nameProductExcludedAttributes] : array();
            $data[self::nameProductName] .= self::getTitleAppendix($data[self::nameProductAttributes],
                array_merge($nameExcludedAttributes, $data[self::nameProductExcludedAttributes]));
        }

        foreach ($nameAttributesOrder as $v) {
            list($k, $v) = each($v);
            if (self::isMeasurable($k)) {
                $value = self::formatPrice($v);
                $units = self::getUnits(self::getAttrValue($v));

                // Don't show 0 values
                $v = doubleval($value) > 0 ? $value . $units : '';
            }

            $attributes .= self::section($k, $v, TRUE, 4);
        }

        $data[self::nameProductAttributes] = $attributes;

        $data[self::nameProductPrice] =
            isset($data[self::nameProductPrice]) && strlen($data[self::nameProductPrice]) > 0
                ? self::formatPrice($data[self::nameProductPrice]) : NULL;

        $data[self::nameProductMarketPrice] =
            isset($data[self::nameProductMarketPrice]) && strlen($data[self::nameProductMarketPrice]) > 0
                ? self::formatPrice($data[self::nameProductMarketPrice]) : NULL;

        $data[self::nameProductAvailableQty] =
            isset($data[self::nameProductAvailableQty]) && strlen($data[self::nameProductAvailableQty]) > 0
                ? intval(ceil($data[self::nameProductAvailableQty])) : NULL;

        /* Product Condition */
        $data[self::nameProductCondition] =
            isset($data[self::nameProductCondition]) ? $data[self::nameProductCondition] : self::conditionSecondhand;
        $data[self::nameProductCondition] = self::setProductCondition($data[self::nameProductCondition]);
        /********************/

        $fullDescription =
            self::swapSummaryDescription($data[self::nameProductSummary], $data[self::nameProductDescription]);

        $data[self::nameProductImageURL] =
            isset($data[self::nameProductImageURL]) ? self::escapeImageUrl($data[self::nameProductImageURL]) : NULL;

        /* FEATURE 3909*/
        //self::excludeImagesWithHttp(self::escapeImageUrl($data[self::nameProductImageURL])) : null;
        /* END FEATURE 3909*/


        /* Images section */
        if (!isset($data[self::nameProductImages]) || !is_array($data[self::nameProductImages])) {
            $data[self::nameProductImages] = array();
        }

        foreach ($data[self::nameProductImages] as &$image) {
            $image = self::escapeImageUrl($image);
        }

        $baseURL = isset($data[self::nameBaseUrl]) ? $data[self::nameBaseUrl] : '';

        $images = array_unique(array_merge($data[self::nameProductImages],
            self::getImagesFromDescription($fullDescription, $baseURL)));
        /* FEATURE 3909*/
        //$data[self::nameProductImages] = self::excludeImagesWithHttp($images);
        /* END 3909   */
        $data[self::nameProductImages] = $images;

        return $data;
    }

    /** Process ProductSummary and ProductDescriptions in accordance with a client's rules.
     *
     * @param string $summary
     * @param string $description
     */
    public static function swapSummaryDescription(&$summary = '', &$description = '') {
        $summary = !isset($summary) || strlen($summary) == 0 ? $description : $summary;
        $description = !isset($description) || strlen($description) == 0 ? $summary : $description;

        $summary = self::encode2utf8($summary);
        $description = self::encode2utf8($description);
        $fullDescription = $description;

        if (strlen($summary) > 0) {
            $isUTF8 = mb_detect_encoding($summary, 'utf-8');
            $description = self::subString($description, 0, 8000, $isUTF8);
            $stripped = self::removeHtmlCharacters($summary);
            $summary =
                self::subString((strlen($stripped) > 0 ? $stripped : self::removeHtmlCharacters($description)), 0, 500,
                    $isUTF8);
        }

        /*
         * return full description
         * @string
         */

        return $fullDescription;
    }

    /**
     * Helper function
     *
     * @param string $string string
     * @param integer $start start pos
     * @param integer $length max length
     * @param string $encoding encoding
     *
     * @return mixed
     */
    private static function subString($string, $start, $length, $encoding) {
        if ($encoding) {
            $string = mb_substr($string, $start, $length, $encoding);
            $result = mb_strlen($string) == $length ?
                $string = mb_substr($string, $start, mb_strrpos($string, ' ', 0, $encoding), $encoding) : $string;

            return $result;
        }

        $string = substr($string, $start, $length);
        $result = strlen($string) == $length ? $string = substr($string, $start, strrpos($string, ' ', 0)) : $string;

        return $result;
    }

    /**
     * Format Price
     *
     * @param string $value value
     *
     * @return string
     */
    public static function formatPrice($value) {
        $value = preg_replace('/[^0-9.-]/', '', $value);

        return !is_numeric($value) ? '' : number_format($value, 2, '.', '');
    }

    public static function encode2utf8($string) {
        $isUTF8 = mb_check_encoding($string, 'utf-8');
        if (!$isUTF8) {
            $tmpString = str_split($string);

            $string = '';
            foreach ($tmpString as &$char) {
                $string .= utf8_encode($char);
            }
        }

        return preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $string);
    }

    public static function removeHtmlCharacters($string) {
        $translationTable = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

        foreach ($translationTable as $char => $entity) {
            $from[] = $entity;
            $to[] = mb_convert_encoding($entity, "UTF-8", "HTML-ENTITIES");
            $from[] = '&#' . ord($char) . ';';
            $to[] = mb_convert_encoding($entity, "UTF-8", "HTML-ENTITIES");
        }

        $clear = str_replace($from, $to, $string);
        $clear = filter_var($clear, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES);
        $clear = preg_replace("/(&[#0-9a-zA-Z]+;)/", '', $clear);

        return trim($clear);
    }

    /**
     * Escape image url
     *
     * @param string $imageUrl url
     *
     * @return string
     */
    private static function escapeImageUrl($imageUrl) {
        if (isset($imageUrl) && strlen($imageUrl) > 0) {
            $urlComponents = parse_url($imageUrl);
            $imageUrl = self::joinUrl($urlComponents, TRUE);
        }

        return $imageUrl;
    }

    /**
     * Join URL
     *
     * @param string $parts parts
     * @param string $encode encode
     *
     * @return string
     */
    private static function joinUrl($parts, $encode) {
        // TODO: add brackets for each condition

        if ($encode) {
            if (isset($parts['user'])) {
                $parts['user'] = rawurlencode($parts['user']);
            }

            if (isset($parts['pass'])) {
                $parts['pass'] = rawurlencode($parts['pass']);
            }

            if (isset($parts['host']) && !preg_match('!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'])) {
                $parts['host'] = rawurlencode($parts['host']);
            }

            if (!empty($parts['path'])) {
                $parts['path'] = preg_replace('!%2F!ui', '/', rawurlencode($parts['path']));
            }

            if (isset($parts['query'])) {
                $params = explode('=', $parts['query']);
                foreach ($params as &$v) {
                    $v = rawurlencode($v);
                }

                $parts['query'] = implode('=', $params);
            }

            if (isset($parts['fragment'])) {
                $parts['fragment'] = rawurlencode($parts['fragment']);
            }
        }

        $url = '';
        if (!empty($parts['scheme'])) {
            $url .= $parts['scheme'] . ':';
        }

        if (isset($parts['host'])) {
            $url .= '//';

            if (isset($parts['user'])) {
                $url .= $parts['user'];
                if (isset($parts['pass'])) {
                    $url .= ':' . $parts['pass'];
                }
                $url .= '@';
            }

            $url .=  (preg_match('!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'])) ?
                '[' . $parts['host'] . ']' : $parts['host'];

            if (isset($parts['port'])) {
                $url .= ':' . $parts['port'];
            }
            if (!empty($parts['path']) && $parts['path'][0] != '/') {
                $url .= '/';
            }
        }

        if (!empty($parts['path'])) {
            $url .= $parts['path'];
        }

        if (isset($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }

    /**
     * Returns string with attributes values in order to to use in the product title
     *
     * @param array $attributes attributes
     * @param array $excludedAttributes exclude attributes
     *
     * @return string
     */
    public static function getTitleAppendix($attributes, $excludedAttributes = array()) {
        $titleAppendix = array();
        array_walk($excludedAttributes, array(__CLASS__, 'getAttrName'));
        $excludedAttributes = array_map(array(__CLASS__, 'sanitize'), $excludedAttributes);
        foreach ($attributes as $name => $value) {
            $name = self::sanitize(self::getAttrName($name, $value));
            if (!in_array($name, $excludedAttributes) && strlen(trim(self::getAttrValue($value))) > 0) {
                if (self::isMeasurable($name)) {
                    $number = self::formatPrice(self::getAttrValue($value));
                    $number = fmod(doubleval($number), 1) > 0 ? $number : intval($number);

                    $units = self::getUnits(self::getAttrValue($value));

                    // Don't add 0 values to Title
                    if (doubleval($number) <= 0) {
                        continue;
                    }

                    $value = $number . $units;
                }

                $titleAppendix[] = array(ucfirst($name) => ucfirst(self::getAttrValue($value)));
            }
        }

        //Only one weight type (Weight or ShippingWeight) should come into a title.
        // ShippingWeight should be added in case when Weight is not set.
        $issetWeight = FALSE;
        $issetShippingWeight = FALSE;
        $shippingWeightKey = NULL;
        foreach ($titleAppendix as $key => $value) {
            if (array_key_exists(self::nameProductAttrWeight, $value)) {
                $issetWeight = TRUE;
            }
            if (array_key_exists(self::nameProductAttrShippingWeight, $value)) {
                $issetShippingWeight = TRUE;
                $shippingWeightKey = $key;
            }
        }

        if ($issetShippingWeight && $issetWeight && isset($shippingWeightKey) && is_numeric($shippingWeightKey)) {
            unset($titleAppendix[$shippingWeightKey]);
        }

        if (!empty($titleAppendix)) {
            return ' - ' . implode(' ', array_map('array_pop', $titleAppendix));
        }

        return '';
    }

    /**
     * Returns name of attribute. Some platforms allows to set many attributes with an identical name.
     * Each one is coming inside a separate array to avoid the problem
     *
     * @param mixed $name name
     * @param array $value value
     *
     * @return string Returns name of attribute as a string
     */
    public static function getAttrName($name, $value) {
        if (is_array($value)) {
            $name = isset($value['name']) ? $value['name'] : '';
        }

        return $name;
    }

    /**
     * Returns scalar value of attribute. Some platforms allows to set many attributes with an identical name.
     * Each one is coming inside a separate array to avoid the problem
     *
     * @param mixed $value value
     *
     * @return mixed Returns scalar value of attribute
     */
    public static function getAttrValue($value) {
        if (is_array($value)) {
            return isset($value['value']) ? $value['value'] : '';
        }

        return $value;
    }

    /**
     * Returns units of measurable value (weight, length, width etc)
     *
     * @param string $value $value is a string like "10.698 kg"
     *
     * @return string Units. For example: kg, lbs, m, km etc
     */
    public static function getUnits($value) {
        return preg_replace('/[^a-zA-Z]/', '', (string)$value);
    }

    public static function getImagesFromDescription($desc, $base_url = '') {
        $desc = self::encode2utf8($desc);
        $images = array();
        $pattern = '/<img[^\>\<]+\>/';

        $isUTF8 = mb_detect_encoding($desc, 'utf-8');
        $matches = array();

        if ($isUTF8) {
            $pattern .= 'u'; /* u - means UTF-8 support. */
        }

        if (self::strPosition($desc, '<img', $isUTF8) !== FALSE) {
            preg_match_all($pattern, $desc, $matches);
        }

        if (!empty($matches)) {
            libxml_use_internal_errors(true);
            foreach ($matches[0] as $img) {
                //We need to parse each <img> separately because DOMDocument() parses corrupted tags unobviously
                // and unexpectedly if we are parsing whole $description.
                $doc = new \DOMDocument();
                $doc->loadHTML('<?xml encoding="UTF-8">' . $img);

                $tags = $doc->getElementsByTagName('img');

                foreach ($tags as $tag) {
                    $tmpImage = $tag->getAttribute('src');
                    $images[] = strpos($tmpImage, 'data:image') === FALSE ? $tmpImage : NULL;
                }
            }
        }

        if (!empty($images)) {
            foreach ($images as $k => $i) {
                if (strpos(strtolower($i), 'http') !== 0 && !empty($base_url)) {
                    $i = $base_url . '/' . $i;
                }
                $images[$k] = self::escapeImageUrl($i);
            }
        }

        return $images;
    }

    /**
     * String Position
     *
     * @param string $haystack haystack
     * @param string $needle needle
     * @param mixed $encoding encoding
     *
     * @return mixed
     */
    private static function strPosition($haystack, $needle, $encoding) {
        if ($encoding) {
            return mb_strpos($haystack, $needle);
        }

        return strpos($haystack, $needle);
    }

    /**
     * Is Measurable
     *
     * @param string $valueName Value Name
     *
     * @return mixed
     */
    public static function isMeasurable($valueName) {
        return in_array(ucfirst($valueName),
            array(self::nameProductAttrWidth, self::nameProductAttrHeight, self::nameProductAttrLength,
                self::nameProductAttrDepth, self::nameProductAttrWeight, self::nameProductAttrShippingWeight));
    }

    /**
     * Build Xml View Product
     *
     * @param array $data data
     *
     * @return string
     */
    private static function buildXmlViewProduct(&$data) {
        $output = self::section(self::nameProductId, $data[self::nameProductId], TRUE, 3);
        $output .= self::section(self::nameProductName, $data[self::nameProductName], TRUE, 3);
        $output .= self::section(self::nameProductCode, $data[self::nameProductCode], TRUE, 3);
        $output .= self::section(self::nameProductCategory, $data[self::nameProductCategory], TRUE, 3);
        $output .= self::section(self::nameProductPrice, $data[self::nameProductPrice], TRUE, 3);
        $output .= self::section(self::nameProductMarketPrice, $data[self::nameProductMarketPrice], TRUE, 3);
        $output .= self::section(self::nameProductAvailableQty, $data[self::nameProductAvailableQty], TRUE, 3);
        $output .= self::section(self::nameProductCondition, $data[self::nameProductCondition], FALSE, 3);
        $output .= self::section(self::nameProductAttributes, $data[self::nameProductAttributes], FALSE, 3);

        if (isset($data[self::nameProductShippingClass])) {
            $output .= self::section(self::nameProductShippingClass, $data[self::nameProductShippingClass], TRUE, 3);
        }

        if (isset($data[self::nameProductImageURL])) {
            $output .= self::section(self::nameProductImageURL, $data[self::nameProductImageURL], TRUE, 3);
        }

        if (isset($data[self::nameProductImages]) && is_array($data[self::nameProductImages])) {
            $outputImages = '';
            foreach ($data[self::nameProductImages] as $image) {
                $outputImages .= self::section(self::nameProductImageURL, $image, TRUE, 4);
            }
            $output .= self::section(self::nameProductImages, $outputImages, FALSE, 3);
        }

        $output .= self::section(self::nameProductSummary,
            !empty($data[self::nameProductSummary]) ? $data[self::nameProductSummary] : $data[self::nameProductName],
            TRUE, 3);

        $strippedDescription = strip_tags($data[self::nameProductDescription], '<img>');
        $output .= self::section(self::nameProductDescription,
            !empty($strippedDescription) ? $data[self::nameProductDescription] : $data[self::nameProductName], TRUE, 3);

        return self::section(self::nameProduct, $output, FALSE, 2);
    }

    public static function getLivePluginVersion() {

        if (is_null(self::$versionInstance)) {
            self::$versionInstance = new Version();
        }

        return self::$versionInstance->getLivePluginVersion();
    }

    /**
     * Exclude images witch use http protocol
     *
     * @param mixed $images full url, array or string
     *
     * @return mixed array or string
     */
    public static function excludeImagesWithHttp($images) {

        if (is_string($images)) {
            return self::isHttps($images) ? $images : '';
        }

        foreach ($images as $key => $image) {
            if (!self::isHttps($image)) {
                unset($images[$key]);
            }
        }

        return $images;
    }

    /**
     * FEATURE 3909
     */

    //    /**
    //     * Check URL Schema. Https - true, else false
    //     * 
    //     * @param string $url URl to check
    //     * 
    //     * @return bool 
    //     */
    //    protected static function isHttps($url) {
    //        $urlScheme = parse_url($url, PHP_URL_SCHEME);
    //        return  $urlScheme == 'https' ? true : false;
    //    }

    /**
     * Set product condition
     *
     * @param integer $condition product condition(0-New, 1-Refurbished, 2-Secondhand)
     *
     * @return string
     */
    protected static function setProductCondition($condition) {

        switch (intval($condition)) {
            case self::conditionNew:
                $result = 'New';
                break;
            case self::conditionRefurbished:
                $result = 'Refurbished';
                break;
            case self::conditionSecondhand:
            default:
                $result = 'Secondhand';
        }

        return $result;
    }

    /**
     * END FEATURE 3909
     */
}
