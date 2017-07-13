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
defined('_JEXEC') or die;

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

//see it in models/form/settings.xml. In accordance with #3628 new toggle should be added only for Joomla platform
define('BIDORBUY_STORE_INTEGRATOR_CATEGORYSLUG_NAME', 'categorySlug');

if (file_exists(dirname(__FILE__) . '/admin/vendor/autoload.php')) {
    require_once(dirname(__FILE__) . '/admin/vendor/autoload.php');
}

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once(dirname(__FILE__) . '/vendor/autoload.php');
}

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php')) {
    require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
}

if (!class_exists('com_bidorbuyStoreIntegratorInstallerScript')) {
    class com_bidorbuyStoreIntegratorInstallerScript {
        /**
         * @var bobsi\Core
         */
        private $bidorbuyStoreIntegrator;

        /**
         * @return bobsi\Core
         */
        public function getBidorbuyStoreIntegrator() {
            return $this->bidorbuyStoreIntegrator;
        }

        public function __construct() {
            $this->bidorbuyStoreIntegrator = $this->coreInitialize();
        }
        
        public function coreInitialize($settings = null) {
            
            $core = new bobsi\Core();
            
            $version = new JVersion();
            
            $dbSettings = array(
                bobsi\Db::SETTING_PREFIX => JFactory::getConfig()->getValue('config.dbprefix'),
                bobsi\Db::SETTING_SERVER => JFactory::getConfig()->getValue('config.host'),
                bobsi\Db::SETTING_USER => JFactory::getConfig()->getValue('config.user'),
                bobsi\Db::SETTING_PASS => JFactory::getConfig()->getValue('config.password'),
                bobsi\Db::SETTING_DBNAME => JFactory::getConfig()->getValue('config.db')
            );
            $core->init(JFactory::getConfig()->getValue('config.sitename'), JFactory::getConfig()->getValue('config.mailfrom'), $version->PRODUCT . ' ' . $version->RELEASE . '.' . $version->DEV_LEVEL . '.' . $version->DEV_STATUS, $settings, $dbSettings);
            
            return $core;
        }

        function install($parent) {
        }

        function uninstall($parent) {
            JFactory::getDBo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getDropTablesQuery());
            JFactory::getDBo()->query();

            JFactory::getConfig()->set('bidorbuy_first_install', false);
        }

        function update($parent) {
        }

        function preflight($type, $parent) {
            $components = JInstaller::getInstance()->discover();
            $c = array();
            foreach ($components as $component) {
                if ($component->type == 'component') {
                    $c[] = $component->name;
                }
            }
            if (!in_array('com_virtuemart', $c)) {
                Jerror::raiseError(null, 'Virtuemart does not installed');
                return false;
            }
        }

        function postflight($type, $parent) {
            if ($type == 'install') {
                self::updateSettings(bobsi\Settings::name, $this->bidorbuyStoreIntegrator->getSettings()->serialize(true));
            }

            JFactory::getDBo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getInstallAuditTableQuery());
            JFactory::getDBo()->query();
            JFactory::getDBo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getInstallTradefeedDataTableQuery());
            JFactory::getDBo()->query();
            JFactory::getDbo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getInstallTradefeedTableQuery());
            JFactory::getDBo()->query();

            //add all products to the queue in case of first activation
            if (!JFactory::getConfig()->get('bidorbuy_first_install', false)) {
                $this->addAllProductsInQueue();
                JFactory::getConfig()->set('bidorbuy_first_install', true);
            }

            $config = JFactory::getConfig();
            $dbName=$config->get('db');
            $dbPrefix=$config->get('dbprefix');
            
            if(!$this->check_field_exist($dbName,$dbPrefix)){
                $this->addAllProductsInQueue(true);
                $this->bobsi_update($dbPrefix);
            }


            $this->installExtensions();
            $this->enablePlugin();
        }

        static public function updateSettings($name, $settings) {
            $db = JFactory::getDBo();

                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__extensions'));
                $query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode(array($name => $settings))));
                $query->where($db->quoteName('name') . ' = ' . $db->quote('bidorbuystoreintegrator'));
            

            $db->setQuery($query);
            $db->query();
        }

        public function addAllProductsInQueue($update = false) {
            /* @var $p VirtueMartModelProduct */
            VmModel::getModel('product')->_noLimit = true;
            $productsIds = VmModel::getModel('product')->sortSearchListQuery();
            $productsIds = array_chunk($productsIds, 500);

            $productStatus = ($update) ? bobsi\Queries::STATUS_UPDATE : bobsi\Queries::STATUS_NEW;

            foreach ($productsIds as $page) {
                JFactory::getDBo()->setQuery($this->bidorbuyStoreIntegrator->getQueries()->getAddJobQueries($page, $productStatus));
                if (!JFactory::getDBo()->query()) {
                    return false;
                }
            }

            return true;
        }

        private function installExtensions() {
            $installer = new JInstaller();
//    $installer->_overwrite = true;

            $pkg_path = JPATH_ADMINISTRATOR . '/components/com_bidorbuystoreintegrator/extensions/';
            $pkgs = array(
                'plg_mvcoverridebidorbuy.zip' => 'System - MVC Override Plugin (for Bidorbuy Store Integrator)'
            );

            foreach ($pkgs as $pkg => $pkgname) {
                $package = JInstallerHelper::unpack($pkg_path . $pkg);
                if (@$installer->install($package['dir'])) { //ignoring warning "Couldn't find constant JPATH_" in J2.5
                    $msgcolor = "#E0FFE0";
                    $msgtext = "$pkgname successfully installed.";
                } else {
                    $msgcolor = "#FFD0D0";
                    $msgtext = "ERROR: Could not install the $pkgname. Please install manually.";
                }
                ?>
                <table style="background-color:<?php echo $msgcolor; ?>; width: 100%;">
                    <tr style="height:30px">
                        <td><?php echo $msgtext; ?></td>
                    </tr>
                </table>
                <?php
                JInstallerHelper::cleanupInstall($pkg_path . $pkg, $package['dir']);
            }
        }

        private function enablePlugin() {
            $db = JFactory::getDBO();
            $db->setQuery('UPDATE #__extensions SET `enabled` = 1 WHERE `element` = "mvcoverridebidorbuy"');

            if ($db->query()) {
                $msgcolor = "#E0FFE0";
                $msgtext = "MVC Override Plugin successfully enabled";
            } else {
                $msgcolor = "#FFD0D0";
                $msgtext = "ERROR: Could not enable the MVC Override Plugin. Please enable manually.";
            }
            echo '
                <table style="background-color:' . $msgcolor . '; width: 100%;">
                    <tr style="height:30px">
                        <td>' . $msgtext . '</td>
                    </tr>
                </table>';
        }

        private function check_field_exist($db, $prefix)
        {
            $check_images_field = "
          SELECT IF(count(*) = 1, true, false) AS result
          FROM
            information_schema.columns
          WHERE
            table_schema = '".$db."'
            AND table_name = '".$prefix.bobsi\Queries::TABLE_BOBSI_TRADEFEED ."'
            AND column_name = 'images';";
            JFactory::getDBo()->setQuery($check_images_field);
            $field = JFactory::getDBo()->loadAssoc();
            
            return $field['result'];
        }

        private function bobsi_update($prefix)
        {
            $query = "ALTER TABLE " . $prefix . bobsi\Queries::TABLE_BOBSI_TRADEFEED . " ADD `images` text AFTER `image_url`";
            JFactory::getDBo()->setQuery($query);
            JFactory::getDBo()->query();
        }
    }

}
