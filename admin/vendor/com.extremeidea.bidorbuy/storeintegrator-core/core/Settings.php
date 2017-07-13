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

/**
 * @SuppressWarnings(PHPMD.ConstantNamingConventions)
 */
class Settings {
    const name = 'bobsiSettings';

    const nameVersion = 'version';

    const nameUsername = 'username';
    const namePassword = 'password';
    const namePasswordPrefix = 'base64:';

    const nameCurrency = 'currency';
    const nameFilename = 'filename';
    const nameCompressLibrary = 'compressLibrary';
    const nameDefaultStockQuantity = 'defaultStockQuantity';
    const nameEnableEmailNotifications = 'enableEmailNotifications';
    const nameEmailNotificationAddresses = 'enableNotificationAddresses';
    const nameLoggingLevel = 'loggingLevel';

    const nameExportQuantityMoreThan = 'exportQuantityMoreThan';
    const nameExcludeCategories = 'excludeCategories';
    const nameExportStatuses = 'exportStatuses';
    const nameExportVisibilities = 'exportVisibilities';

    const nameTokenDownload = 'tokenDownloadUrl';
    const nameTokenExport = 'tokenExportUrl';

    const nameLoggingForm = 'loggingForm';
    const nameLoggingFormAction = 'loggingFormAction';
    const nameLoggingFormButton = 'loggingFormButton';
    const nameLoggingFormFilename = 'loggingFormFilename';
    const nameLoggingFormActionDownload = 'download';
    const nameLoggingFormActionRemove = 'remove';

    const paramToken = 't';
    const paramCategory = 'c';
    const paramCallbackExportProducts = 'callbackExportProducts';
    const paramCallbackGetProducts = 'callbackGetProducts';
    const paramCallbackGetBreadcrumb = 'callbackGetBreadcrumb';
    const paramCategories = 'categories';
    const paramItemsPerIteration = 'itemsPerIteration';
    const paramIteration = 'iteration';
    const paramRevision = 'revision';
    const paramCategoryId = 'categoryId';
    const paramVariationId = 'variationId';
    const paramIds = 'ids';
    const paramProductStatus = 'productStatus';
    const paramTimeStart = 'timestart';
    const paramCategoryBreadcrumb = 'categoryBreadcrumb';
    const paramExtensions = 'extensions';

    const nameWordings = self::name;
    const nameWordingsTitle = 'title';
    const nameWordingsDescription = 'description';
    const nameWordingsValidator = 'validator';
    const nameWordingsValidatorError = 'validatorError';

    public static $dataPath = '.';
    public static $logsPath = '.';

    public static $storeEmail = '';
    public static $storeName = '';

    const nameExportConfiguration = 'exportConfiguration';
    const nameExportCriteria = 'exportCriteria';
    const nameExportLinks = 'exportLinks';
    const nameAdvancedSettings = 'advanced';

    const nameExportUrl = 'exportUrl';
    const nameDownloadUrl = 'downloadUrl';
    const nameButtonExport = 'exportTradefeed';
    const nameButtonDownload = 'downloadTradefeed';
    const nameButtonReset = 'resetExportUrls';
    const nameButtonResetAudit = 'resetExportTables';
    const nameActionReset = 'resetTokens';
    const nameActionResetExportTables = 'resetaudit';
    /*
     * Feature #3750
     */
    const nameExportProductSummary = 'ProductSummaryExport';
    const nameExportProductDescription = 'ProductDescriptionExport';
    /*
     * End Feature Block
     */

    private $settings;
    private $defaults;

    public function __construct() {
        $this->defaults = array(
            self::nameVersion => '1.0',
            self::nameUsername => '',
            self::namePassword => '',
            self::nameCurrency => '',
            self::nameFilename => 'tradefeed',
            self::nameCompressLibrary => 'none',
            self::nameDefaultStockQuantity => 5,
            self::nameExportQuantityMoreThan => 0,
            self::nameExportStatuses => array(),
            self::nameExportVisibilities => array(),
            self::nameExcludeCategories => array(),
            self::nameEnableEmailNotifications => 0,
            self::nameEmailNotificationAddresses => '',
            self::nameLoggingLevel => 'error',
            self::nameTokenDownload => self::generateToken(),
            self::nameTokenExport => self::generateToken(),
            /*
             * Feature #3750
             */
            self::nameExportProductSummary => true,
            self::nameExportProductDescription => true,

            /*
             * End Feature Block
             */

            self::nameWordings => array(
                self::nameUsername => array(
                    self::nameWordingsTitle => 'Username',
                    self::nameWordingsDescription => 'Please specify the username if your platform is protected by <a href=\'http://en.wikipedia.org/wiki/Basic_access_authentication\' target=\'_blank\'>Basic Access Authentication</a>',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::namePassword => array(
                    self::nameWordingsTitle => 'Password',
                    self::nameWordingsDescription => 'Please specify the password if your platform is protected by <a href=\'http://en.wikipedia.org/wiki/Basic_access_authentication\' target=\'_blank\'>Basic Access Authentication</a>',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameCurrency => array(
                    self::nameWordingsTitle => 'Export currency',
                    self::nameWordingsDescription => 'If not selected, the default currency is used.',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameFilename => array(
                    self::nameWordingsTitle => 'Export filename',
                    self::nameWordingsDescription => '16 characters max. Must start with a letter.<br>Can contain letters, digits, "-" and "_"',
//                    self::nameWordingsValidator => function ($value) {
//                        return !empty($value) && strlen($value) <= 16 && preg_match('/^[a-z0-9]+([a-z0-9-_]+)?$/iD', $value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNameFileName'),
                ),
                self::nameCompressLibrary => array(
                    self::nameWordingsTitle => 'Compress Tradefeed XML',
                    self::nameWordingsDescription => 'Choose a Compress Library to compress destination Tradefeed XML',
//                    self::nameWordingsValidator => function ($value) {
//                        return array_key_exists($value, Settings::getCompressLibraryOptions());
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNameCompressLibrary'),
                ),
                self::nameDefaultStockQuantity => array(
                    self::nameWordingsTitle => 'Min quantity in stock',
                    self::nameWordingsDescription => 'Set minimum quantity if quantity management is turned OFF',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_numeric($value) && intval($value) >= 0;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNameDefaultStockQuantity'),
                ),
                self::nameExportQuantityMoreThan => array(
                    self::nameWordingsTitle => 'Export products with available quantity more than',
                    self::nameWordingsDescription => 'Products with stock quantities lower than this value will be excluded from the XML feed',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_numeric($value) && intval($value) >= 0;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNameExportQuantityMoreThan'),
                ),
                self::nameExportStatuses => array(
                    self::nameWordingsTitle => 'Export statuses',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_array($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsArray'),
                ),
                self::nameExportVisibilities => array(
                    self::nameWordingsTitle => 'Export visibilities',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_array($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsArray'),
                ),
                self::nameEnableEmailNotifications => array(
                    self::nameWordingsTitle => 'Turn on/off email notifications',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_bool($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsBool'),
                ),
                self::nameEmailNotificationAddresses => array(
                    self::nameWordingsTitle => 'Send logs to email address(es)',
                    self::nameWordingsDescription => 'Specify email address(es) separated by comma to send the log entries to',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_string($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsString'),
                ),
                self::nameLoggingLevel => array(
                    self::nameWordingsTitle => 'Logging Level',
                    self::nameWordingsDescription => 'A level describes the severity of a logging message. There are six levels, show here in descending order of severity',
//                    self::nameWordingsValidator => function ($value) {
//                        return in_array($value, Settings::getLoggingLevelOptions());
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNameLoggingLevel'),
                ),
                self::nameExcludeCategories => array(
                    self::nameWordingsTitle => 'Included Categories',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_array($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsArray'),
                ),
                self::nameExportConfiguration => array(
                    self::nameWordingsTitle => 'Export Configuration',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameExportCriteria => array(
                    self::nameWordingsTitle => 'Export Criteria',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameExportLinks => array(
                    self::nameWordingsTitle => 'Links',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameExportUrl => array(
                    self::nameWordingsTitle => 'Export',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_string($value) && !empty($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNotEmpty'),
                ),
                self::nameDownloadUrl => array(
                    self::nameWordingsTitle => 'Download',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return is_string($value) && !empty($value);
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateNotEmpty'),
                ),
                self::nameButtonExport => array(
                    self::nameWordingsTitle => 'Export Tradefeed',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array($this, '__validate_true'),
                ),
                self::nameButtonDownload => array(
                    self::nameWordingsTitle => 'Download Tradefeed',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameButtonReset => array(
                    self::nameWordingsTitle => 'Reset tokens',
                    self::nameWordingsDescription => '',
//                    self::nameWordingsValidator => function ($value) {
//                        return true;
//                    },
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameAdvancedSettings => array(
                    self::nameWordingsTitle => 'Advanced',
                    self::nameWordingsDescription => '',
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                self::nameActionResetExportTables => array(
                    self::nameWordingsTitle => 'Reset export data',
                    self::nameWordingsDescription => 'Clicking on this link will reset all exported data in your tradefeed. This is done by clearing all exported product data, before re-adding all products to the export and completing the query. Please note, you will still need to run the export link once this process completes in order to update the download file.',
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', '__validate_not_empty'),
                ),
                self::nameButtonResetAudit => array(
                    self::nameWordingsTitle => 'Launch',
                    self::nameWordingsDescription => '',
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateTrue'),
                ),
                /*
                 * Feature #3750
                 */
                self::nameExportProductSummary => array(
                    self::nameWordingsTitle => 'Export Product Summary',
                    self::nameWordingsDescription => 'Check to export product summary to tradefeed',
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsBool'),
                ),

                self::nameExportProductDescription => array(
                    self::nameWordingsTitle => 'Export Product Description',
                    self::nameWordingsDescription => 'Check to export product description to tradefeed',
                    self::nameWordingsValidator => array('com\extremeidea\bidorbuy\storeintegrator\core\Settings', 'validateIsBool'),
                ),
                /*
                 * End Feature Block
                 */
            ),
        );

        $this->settings = $this->defaults;
        unset($this->settings[self::nameWordings]);
    }

    public static function validateTrue() {
        return true;
    }

    public static function validateIsArray($value) {
        return is_array($value);
    }

    public static function validateIsBool($value) {
        return is_bool($value);
    }

    public static function validateIsString($value) {
        return is_string($value);
    }

    public static function validateNotEmpty($value) {
        return is_string($value) && !empty($value);
    }

    public static function validateNameFileName($value) {
        return !empty($value) && strlen($value) <= 16 && preg_match('/^[a-z]+([a-z0-9-_]+)?$/iD', $value);
    }

    public static function validateNameCompressLibrary($value) {
        return array_key_exists($value, self::getCompressLibraryOptions());
    }

    public static function validateNameDefaultStockQuantity($value) {
        return is_numeric($value) && intval($value) >= 0;
    }

    public static function validateNameExportQuantityMoreThan($value) {
        return is_numeric($value) && intval($value) >= 0;
    }

    public static function validateNameLoggingLevel($value) {
        return in_array($value, self::getLoggingLevelOptions());
    }

    public function getUsername() {
        return $this->settings[self::nameUsername];
    }

    public function setUsername($Username) {
        $this->settings[self::nameUsername] = $Username;
    }

    public function getPassword() {
        $password = $this->settings[self::namePassword];

        if (!empty($password) && strpos($password, self::namePasswordPrefix) == 0) {
            $length = strlen(self::namePasswordPrefix);
            $password = base64_decode(substr($password, $length, strlen($password) - $length));
        }

        return $password;
    }

    public function setPassword($password) {
        if (!empty($password)
            && strpos($password, self::namePasswordPrefix) === false
        ) {

            $this->settings[self::namePassword] =
                self::namePasswordPrefix . base64_encode($password);
        }
    }

    public function getCurrency() {
        return $this->settings[self::nameCurrency];
    }

    public function getFilename() {
        return $this->settings[self::nameFilename];
    }

    public function getProtectedExtension() {
        return '.dat';
    }

    public function getDefaultExtension() {
        $options = $this->getCompressLibraryOptions();
        return $options['none']['extension'] . $this->getProtectedExtension();
    }

    public function getCompressLibrary() {
        return $this->settings[self::nameCompressLibrary];
    }

    public function getExportQuantityMoreThan() {
        return $this->settings[self::nameExportQuantityMoreThan];
    }

    public function setExportQuantityMoreThan($value) {
        $this->settings[self::nameExportQuantityMoreThan] = intval($value);
    }

    public function getDefaultStockQuantity() {
        return $this->settings[self::nameDefaultStockQuantity];
    }

    public function setDefaultStockQuantity($value) {
        $this->settings[self::nameDefaultStockQuantity] = intval($value);
    }

    public function getExportStatuses() {
        return $this->settings[self::nameExportStatuses];
    }

    public function setExportStatuses($value = array()) {
        $wordings = $this->getDefaultWordings();

        $status = call_user_func($wordings[self::nameExportStatuses][self::nameWordingsValidator], $value);
        if ($status) {
            $this->settings[self::nameExportStatuses] = $value;
        }

        return $status;
    }

    public function getExportVisibilities() {
        return $this->settings[self::nameExportVisibilities];
    }

    public function setExportVisibilities($value = array()) {
        $wordings = $this->getDefaultWordings();

        $status = call_user_func($wordings[self::nameExportVisibilities][self::nameWordingsValidator], $value);
        if ($status) {
            $this->settings[self::nameExportVisibilities] = $value;
        }

        return $status;
    }

    public function getExcludeCategories() {
        return $this->settings[self::nameExcludeCategories];
    }

    public function setExcludeCategories($value = array()) {
        $wordings = $this->getDefaultWordings();

        $status = call_user_func($wordings[self::nameExcludeCategories][self::nameWordingsValidator], $value);
        if ($status) {
            $this->settings[self::nameExcludeCategories] = $value;
        }

        return $status;
    }

    public function getEnableEmailNotifications() {
        return $this->settings[self::nameEnableEmailNotifications];
    }

    public function getEmailNotificationAddresses() {
        return $this->settings[self::nameEmailNotificationAddresses];
    }

    public function getLoggingLevel() {
        return $this->settings[self::nameLoggingLevel];
    }

    public function getTokenDownload() {
        return $this->settings[self::nameTokenDownload];
    }

    public function setTokenDownload($value) {
        $this->settings[self::nameTokenDownload] = $value;
    }

    public function getTokenExport() {
        return $this->settings[self::nameTokenExport];
    }

    public function setTokenExport($value) {
        $this->settings[self::nameTokenExport] = $value;
    }

    public static function getCompressLibraryOptions() {
        $value['none'] = array('extension' => '.xml', 'mime-type' => 'text/xml');

        if (extension_loaded('zip')) {
            $value['zip'] = array('extension' => '.zip', 'mime-type' => 'application/zip');
        }

        if (extension_loaded('zlib')) {
            $value['gzip'] = array('extension' => '.xml.gz', 'mime-type' => 'application/x-gzip');
        }

        return $value;
    }

    public static function getLoggingLevelOptions() {
        return array('all', 'fatal', 'error', 'warn', 'info', 'debug', 'trace');
    }

    public static function generateToken() {
        return md5(time() . rand(0, 100));
    }

    public function getOutputFile() {
        return self::$dataPath . '/' . $this->getFilename() . $this->getDefaultExtension();
    }

    public function getCategoryOutputFile($categoryId) {
        return self::$dataPath . '/' . $this->getFilename() . '.' . $categoryId . $this->getDefaultExtension();
    }

    public function getCategoryTemporaryOutputFile($categoryId) {
        return self::$dataPath . '/' . $this->getFilename() . '.' . $categoryId . $this->getProtectedExtension();
    }

    public function getCategoryOutputFilePattern($type = 'all') {
        switch ($type) {
            case 'completed' :
                return "/^{$this->getFilename()}\.\d+({$this->getDefaultExtension()})$/i";
                break;
            case 'md5' :
                return "/^{$this->getFilename()}\.[a-z0-9]+({$this->getDefaultExtension()})$/i";
                break;
            default:
                return "/^{$this->getFilename()}\.\d+({$this->getDefaultExtension()})|({$this->getProtectedExtension()})$/i";
        }
    }

    public function getCompressOutputFile() {
        $options = $this->getCompressLibraryOptions();
        return self::$dataPath . '/' . $this->getFilename() . $options[$this->getCompressLibrary()]['extension'] . $this->getProtectedExtension();
    }

    /*
     * Feature #3750
     */
    public function getExportProductSummary() {
        return $this->settings[self::nameExportProductSummary];
    }

    public function setExportProductSummary($value) {
        $this->settings[self::nameExportProductSummary] = $value;
    }

    public function getExportProductDescription() {
        return $this->settings[self::nameExportProductDescription];
    }

    public function setExportProductDescription($value) {
        $this->settings[self::nameExportProductDescription] = $value;
    }

    /*
     * End Feature Block
     */

    public function cleanProtectedExtension($file) {
        return str_replace($this->getProtectedExtension(), '', $file);
    }

    public function getDefaultWordings() {
        return $this->defaults[self::nameWordings];
    }

    public function serialize($base64 = 0) {
        return $this->serialize2($this->settings, $base64);
    }

    public function serialize2($settings = array(), $base64 = 0) {
        $data = serialize($settings);
        if ($base64) {
            $data = base64_encode($data);
        }

        return $data;
    }

    public function unserialize($settings, $base64 = 0) {
        if ($base64) {
            $settings = base64_decode($settings);
        }

        $settings = unserialize($settings);

        $defaults = $this->defaults;
        unset($defaults[self::nameWordings]);

        !is_array($settings) ?
            $settings = $defaults : $settings = array_merge($defaults, $settings);


        $password = $settings[self::namePassword];
        if (!empty($password) && strpos($password, self::namePasswordPrefix) === false) {
            $settings[self::namePassword] = self::namePasswordPrefix . base64_encode($password);
        }

        $this->settings = $settings;
    }
}

Settings::$dataPath = dirname(__FILE__) . '/../data';
Settings::$logsPath = dirname(__FILE__) . '/../logs';

Settings::$storeEmail = '';
Settings::$storeName = '';
