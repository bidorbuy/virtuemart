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

jimport('joomla.version');

// workaround to load JController
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.user.authorization');
class_exists('JController');
class_exists('JAuthorization');
class_exists('JModel');

// import Joomla controller library
jimport('joomla.installer.installer');
//require_once(JPATH_LIBRARIES . '/joomla/application/component/controller.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR  . '/vendor/autoload.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR  . '/script.php');
require_once(dirname(__FILE__) . '/bidorbuystoreintegratorClasses.php');

// Getting VM version
require_once(JPATH_ADMINISTRATOR .  '/components/com_virtuemart/version.php');

class  BidorbuyStoreIntegratorController extends BidorbuyStoreIntegratorControllerVM2
{
}


