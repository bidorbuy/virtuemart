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

// import joomla controller library
jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');

// Get an instance of the controller prefixed by BidorbuyStoreIntegrator
$controller = JControllerLegacy::getInstance('BidorbuyStoreIntegrator');
// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->get('task', '', 'STR'));

// Redirect if set by the controller
$controller->redirect();