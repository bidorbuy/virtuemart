<?php

$jver = new JVersion();
defined('JVER') OR define('JVER', $jver->RELEASE);


(!defined('BOBSI_PATH_TO_HELPER') && JVER == 2.5) ?
    define('BOBSI_PATH_TO_HELPER', '/joomla/application/module/helper.php')
    : define('BOBSI_PATH_TO_HELPER', '/cms/module/helper.php');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('VMPATH_ADMIN') or define('VMPATH_ADMIN', JPATH_ROOT . DS . 'administrator'
    . DS . 'components' . DS . 'com_virtuemart');
