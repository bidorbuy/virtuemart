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
 * Class Warnings
 */
class Warnings {

    /**
     * System Warnings
     *
     * @var array $systemWarnings contains system warnings
     */
    protected $systemWarnings = array();

    /**
     * Business Warnings
     *
     * @var array $businessWarnings contains business warnings
     */
    protected $businessWarnings = array(
        "bidorbuy Store Integrator warning: bidorbuy will not support non SSL images URLs from June 2017 - any products with insecure content will not be exported. <a href='https://support.bidorbuy.co.za/Knowledgebase/Article/View/220/0/https-images-requirement'>More details</a>",
    );

    /**
     * Add new warning in the system warning array
     *
     * @param string $warning warning to add to the array
     *
     * @return void
     */
    public function setSystemWarning($warning) {
        $this->systemWarnings[] = $warning;
    }

    /**
     * Add new warning in the business warning array
     *
     * @param string $warning warning to add to the array
     *
     * @return void
     */
    public function setBusinessWarning($warning) {
        $this->businessWarnings[] = $warning;
    }

    /**
     * Get all warnings stored in array systemWarnings
     *
     * @return array system warnings
     */
    public function getSystemWarnings() {
        return $this->systemWarnings;
    }

    /**
     * Get all warnings stored in array businessWarnings
     *
     * @return array system warnings
     */
    public function getBusinessWarnings() {
        return $this->businessWarnings;
    }

}
