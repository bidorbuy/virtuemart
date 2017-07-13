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
 * Class Queries
 *
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 */
class Queries {
    const TABLE_BOBSI_TRADEFEED = 'bobsi_tradefeed_product';
    const TABLE_BOBSI_TRADEFEED_TEXT = 'bobsi_tradefeed_product_base';
    const TABLE_BOBSI_TRADEFEED_AUDIT = 'bobsi_tradefeed_audit';
    const ROW_NAME = 'a';

    const STATUS_NEW = 'N';
    const STATUS_DELETE = 'D';
    const STATUS_UPDATE = 'U';
    const STATUS_PROCESSING = 'P';

    const PRODUCT_PROCESS_TIMEOUT = 1800; // 30 min for processing ~500 products

    private $tablePrefix = '';
    private $tradefeed;

    /**
     * Queries constructor.
     *
     * @param string $tablePrefix table prefix
     * @param Tradefeed $tradefeed Tradefeed instance
     *
     * @return mixed
     */
    public function __construct($tablePrefix, Tradefeed $tradefeed) {
        $this->tablePrefix = $tablePrefix;
        $this->tradefeed = $tradefeed;
    }

    /**
     * SQL query for create main products table
     *
     * @return string
     */
    public function getInstallTradefeedTableQuery() {
        $tradefeedTable = "
              `product_id` bigint(20) NOT NULL,
              `variation_id` bigint(20) NOT NULL DEFAULT '0',
              `category_id` varchar(1000) NOT NULL DEFAULT '0',
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `row_created_on` datetime NOT NULL,
              `row_modified_on` datetime NOT NULL,
              `code` varchar(128) NOT NULL,
              `name` varchar(512)NOT NULL,
              `category` varchar(2000) NOT NULL,
              `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
              `market_price` decimal(20,6) DEFAULT '0.000000',
              `available_quantity` int(10) NOT NULL DEFAULT '0',
              `condition` varchar(16) NOT NULL,
              `image_url` varchar(1024),
              `images` text,
              `shipping_product_class` varchar(255),
              `attr_custom_attrs` text,
              PRIMARY KEY (`id`)
              ";

        return $this->createTable(
            $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, $tradefeedTable
        );
    }

    /**
     * Create table for summary and description columns
     *
     * @return string
     */
    public function getInstallTradefeedDataTableQuery() {
        $descriptionTable = "
            `product_id` bigint(20) NOT NULL,
            `summary` varchar(500),
            `description` varchar(8000),
            PRIMARY KEY (`product_id`)
            ";
        return $this->createTable(
            $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_TEXT, $descriptionTable
        );
    }

    /**
     * SQL query for create audit table
     *
     * @return string
     */
    public function getInstallAuditTableQuery() {
        $auditTable = "
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `product_id` bigint(20) NOT NULL,
            `product_status` char(1) NOT NULL,
            `row_created_on` datetime NOT NULL,
            `row_updated_on` datetime NOT NULL,
            `row_status` char(1) NOT NULL DEFAULT '" . self::STATUS_NEW . "',
            PRIMARY KEY (`id`)
            ";
        return $this->createTable(
            $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, $auditTable
        );
    }

    /**
     * Get job queries
     *
     * @param array $productIds product id's
     * @param string $productStatus new, delete, update
     *
     * @return string
     */
    public function getAddJobQueries($productIds, $productStatus) {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $data = array();
        $date = date("Y-m-d H:i:s");
        foreach ($productIds as $productsId) {
            $data[] = array((int)$productsId, $productStatus, $date, $date);
        }

        return $this->insert(
            $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT,
            array('product_id', 'product_status', 'row_created_on', 'row_updated_on'),
            $data, false
        );
    }

    /**
     * Delete jobs
     *
     * @param array $ids products id's
     *
     * @return string
     */
    public function getDeleteJobs(array $ids) {
        return $this->delete($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, "`id` IN (" . implode(',', $ids) . ")");
    }

    /**
     * Clear finished jobs
     *
     * @param int $timeStart time start
     *
     * @return array of queries
     */
    public function getClearJobsQueries($timeStart) {
        return array(
            //delete all jobs marked as DELETE
            'clear_finished_jobs' => $this->delete($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, "`row_status` = '" . self::STATUS_DELETE . "'"),
            //then mark jobs that not finished more that self::PRODUCT_PROCESS_TIMEOUT minutes ago as new.
            'return_unfinished_jobs' => $this->update(
                $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT,
                array('product_status' => self::STATUS_UPDATE, 'row_updated_on' => date("Y-m-d H:i:s"), 'row_status' => self::STATUS_NEW),
                "`row_status` = '" . self::STATUS_PROCESSING . "' AND `row_updated_on` < '" . date("Y-m-d H:i:s", $timeStart - self::PRODUCT_PROCESS_TIMEOUT) . "'"
            )
        );
    }

    /**
     * Set jobs
     *
     * @param array $productsIds products ids
     * @param string $status product status N, U, D
     * @param integer $timeStart timestamp
     *
     * @return string
     */
    public function getSetJobsRowStatusQuery($productsIds, $status, $timeStart) {
        return $this->update($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, array('row_updated_on' => date("Y-m-d H:i:s"), 'row_status' => $status), "`product_id` IN (" . $productsIds . ") AND `row_created_on` < '" . date("Y-m-d H:i:s", $timeStart) . "'");
    }

    /**
     * Get jobs status
     *
     * @param string $status product status N, U, D
     * @param integer $timeStart timestamp
     *
     * @return string
     */
    public function getJobsByStatusQuery($status, $timeStart) {
        return $this->select($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, '`product_id` as id', "`row_status` = '" . self::STATUS_NEW . "' AND `product_status` = '" . $status . "' AND `row_created_on` < '" . date("Y-m-d H:i:s", $timeStart) . "'");
    }

    /**
     * Insert products query
     *
     * @param array $keys product columns
     * @param array $values product values
     *
     * @return string
     */
    public function getBulkInsertProductQuery(array $keys, array $values) {
        return $this->insert($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, $keys, $values, false);
    }

    /**
     * Insert products summary & description
     *
     * @param integer $pid product id
     * @param array $values product values
     *
     * @return string
     */
    public function getInsertProductDataQuery($pid, array $values) {
        return $this->insert($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_TEXT, array('product_id', 'summary', 'description'), array(array($pid, $values['summary'], $values['description'])), true);
    }

    /**
     * Delete products from tradefeed
     *
     * @param array $ids product ids
     *
     * @return string
     */
    public function getRemoveProductsFromTradefeedQuery($ids) {
        return $this->delete($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, "`product_id` IN (" . $ids . ")");
    }

    /**
     * Get product data query
     *
     * @return string
     */
    public function getTradefeedDataQuery() {
        return $this->select($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, '`product_id`, `name`, `code`, `category`, `price`, `market_price`, `available_quantity`, `condition`, `attr_custom_attrs`, `shipping_product_class`, `image_url`, `summary`, `description`');
    }

    /**
     * Drop all tradefeed tables
     *
     * @return string
     */
    public function getDropTablesQuery() {
        return $this->drop(array($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_TEXT));
    }

    /**
     * Get all untouched products
     *
     * @param array $ids products ids
     *
     * @return string
     */
    public function getSelectUntouchedProducts($ids) {
        return $this->select($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT, '`product_id` as id', "`product_id` IN (" . $ids . ") AND `row_status` = '" . self::STATUS_NEW . "'", true);
    }

    /**
     * Truncate Audit table
     * 
     * @return string
     */
    public function getTruncateJobsQuery() {
        return "TRUNCATE TABLE `" . $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_AUDIT . "`";
    }

    /**
     * Truncate products table
     *
     * @return string
     */
    public function getTruncateProductQuery() {
        return "TRUNCATE TABLE `" . $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED . "`";
    }

    /**
     * Get all data from tradefeed tables
     * to create XML file
     *
     * @param array $categoryIds products categories
     *
     * @return array
     */
    public function getXmlViewDataQuery($categoryIds) {
        if (!is_array($categoryIds)) {
            $categoryIds = array($categoryIds);
        }
        $whereCategories = array();
        foreach ($categoryIds as $categoryId) {
            $whereCategories[] = "t.`category_id` LIKE '%" . Tradefeed::categoryIdDelimiter . $categoryId . Tradefeed::categoryIdDelimiter . "%'";
        }
        $query = array();
        $query[] = $this->tradefeed->section(Tradefeed::nameProductId, "', t.`product_id`, '", true, 3) . "'";
        $query[] = "'" . $this->tradefeed->section(Tradefeed::nameProductCode, "', t.`code`, '", true, 3) . "'";
        $query[] = "'" . $this->tradefeed->section(Tradefeed::nameProductName, "', t.`name`, '", true, 3) . "'";
        $query[] = "IF(LENGTH(t.`category`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductCategory, "', t.`category`, '", true, 3) . "'), '')";
//        $query[] = "IF(t.`price` > 0, CONCAT('" . Tradefeed::section(Tradefeed::nameProductPrice, "', REPLACE(FORMAT(t.`price`, 0), ',', ''), '", true, 3) . "'), '')";
//        $query[] = "IF(t.`market_price` > 0, CONCAT('" . Tradefeed::section(Tradefeed::nameProductMarketPrice, "', REPLACE(FORMAT(t.`market_price`, 0), ',', ''), '", true, 3) . "'), '')";
        $query[] = "'" . $this->tradefeed->section(Tradefeed::nameProductPrice, "', REPLACE(FORMAT(t.`price`, 2), ',', ''), '", true, 3) . "'";
        $query[] = "IF(t.`market_price` > 0, CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductMarketPrice, "', REPLACE(FORMAT(t.`market_price`, 2), ',', ''), '", true, 3) . "'), '')";
        $query[] = "'" . $this->tradefeed->section(Tradefeed::nameProductAvailableQty, "', t.`available_quantity`, '", true, 3) . "'";
        $query[] = "'" . $this->tradefeed->section(Tradefeed::nameProductCondition, "', t.`condition`, '", true, 3) . "'";
        $query[] = "IF(LENGTH(t.`image_url`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductImageURL, "', t.`image_url`, '", true, 3) . "'), '')";
        $query[] = "IF(LENGTH(t.`images`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductImages, "', t.`images`, '", false, 3, true) . "'), '')";
        $query[] = "IF(LENGTH(au.`summary`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductSummary, "', au.`summary`, '", true, 3) . "'), '')";
        $query[] = "IF(LENGTH(au.`description`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductDescription, "', au.`description`, '", true, 3) . "'), '')";
        $query[] = "IF(LENGTH(t.`shipping_product_class`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductShippingClass, "', t.`shipping_product_class`, '", true, 3) . "'), '')";
        $query[] = "IF(LENGTH(t.`attr_custom_attrs`), CONCAT('" . $this->tradefeed->section(Tradefeed::nameProductAttributes, "', t.`attr_custom_attrs`, '", false, 3, true) . "'), ''),'";

        $query = $this->tradefeed->section(Tradefeed::nameProduct, join(',', $query), false, 2);
        $query = "SELECT CONCAT('" . $query;

        $where = empty($whereCategories) ? '' : ' AND (' . join(' OR ', $whereCategories) . ')';

        $query .= "') AS " . self::ROW_NAME . " FROM " . $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED . " AS t, " . $this->tablePrefix . self::TABLE_BOBSI_TRADEFEED_TEXT . " AS au
            WHERE t.`product_id` = au.`product_id`" . $where . ' group by t.`code`';

        return $query;
    }

    /**
     * Get products count
     *
     * @return string
     */
    public function getXmlViewDataCountQuery() {
        return $this->select($this->tablePrefix . self::TABLE_BOBSI_TRADEFEED, 'COUNT(*) AS ' . self::ROW_NAME);
    }

    /**
     * Create table function
     *
     * @param string $tableName table name
     * @param string $fields table field
     *
     * @return string query
     */
    private function createTable($tableName, $fields) {
        return "CREATE TABLE IF NOT EXISTS `$tableName` (" . $fields . ") ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci";
    }

    /**
     * Insert data into tables
     *
     * @param string $tableName table name
     * @param array $keys Array of keys.
     * @param array $values Array of arrays.
     * @param bool $replace replace or insert
     *
     * @return string
     */
    private function insert($tableName, $keys, $values, $replace) {
        $insert = $replace ? "REPLACE" : "INSERT";
        $data = array();
        foreach ($values as $value) {
            $data[] = "('" . join("','", $value) . "')";
        }
        return "$insert INTO `$tableName` (`" . join("`, `", $keys) . "`) VALUES " . join(', ', $data);
    }

    /**
     * Get data from tables
     *
     * @param string $tableName table name
     * @param string $fieldsToSelect fields
     * @param string $where The WHERE clause is used to filter records.
     * @param int $isDistinct return only distinct (different) values
     *
     * @return string
     */
    private function select($tableName, $fieldsToSelect = '*', $where = '', $isDistinct = 0) {
        $isDistinct = $isDistinct ? 'DISTINCT' : '';
        $where = $where ? "WHERE $where" : "";
        return "SELECT $isDistinct $fieldsToSelect FROM `$tableName` $where";
    }

    /**
     * Delete data from tables
     *
     * @param string $tableName table name
     * @param string $where The WHERE clause is used to filter records.
     *
     * @return string
     */
    private function delete($tableName, $where) {
        $where = $where ? "WHERE $where" : "";
        return "DELETE FROM `$tableName` $where";
    }

    /**
     * Update data in DB query
     *
     * @param string $tableName table name
     * @param array $fields needed fields
     * @param string $where The WHERE clause is used to filter records.
     *
     * @return string
     */
    private function update($tableName, $fields, $where = '') {
        $where = $where ? "WHERE $where" : "";

        $set = '';
        foreach ($fields as $key => $field) {
            $set = "`$key` = '$field'";
        }
        return "UPDATE `$tableName` SET $set $where";
    }

    /**
     * Drop table(s)
     *
     * @param mixed $tableNames table name
     *
     * @return string
     */
    private function drop($tableNames) {
        if (is_array($tableNames)) {
            $tableNames = "`" . join("`,`", $tableNames) . "`";
        }
        return "DROP TABLE IF EXISTS $tableNames";
    }
}
