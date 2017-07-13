<?php
namespace com\extremeidea\php\tools\log4php; 
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * 
 *		http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package log4php
 */

 //We use autoloader from composer. So we don't need built-in autoloder and it has been disabled in order to avoid conflicts with other outside autoloaders (e.g. in Magento).
/* 
if (!defined('WARN_ON_AUTOLOAD_IGNORE') && function_exists('__autoload')) {
	trigger_error("log4php: It looks like your code is using an __autoload() function. log4php uses spl_autoload_register() which will bypass your __autoload() function and may break autoloading.", E_USER_WARNING);
}

spl_autoload_register(array('LoggerAutoloader', 'autoload'));
*/
/**
 * Class autoloader.
 * 
 * @package log4php
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version $Revision: 176 $
 */
class LoggerAutoloader {
	
	/** Maps classnames to files containing the class. */
	private static $classes = array(
	
		// Base
		'com\extremeidea\php\tools\log4php\LoggerAppender' => '/LoggerAppender.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderPool' => '/LoggerAppenderPool.php',
		'com\extremeidea\php\tools\log4php\LoggerConfigurable' => '/LoggerConfigurable.php',
		'com\extremeidea\php\tools\log4php\LoggerConfigurator' => '/LoggerConfigurator.php',
		'com\extremeidea\php\tools\log4php\LoggerException' => '/LoggerException.php',
		'com\extremeidea\php\tools\log4php\LoggerFilter' => '/LoggerFilter.php',
		'com\extremeidea\php\tools\log4php\LoggerHierarchy' => '/LoggerHierarchy.php',
		'com\extremeidea\php\tools\log4php\LoggerLevel' => '/LoggerLevel.php',
		'com\extremeidea\php\tools\log4php\LoggerLocationInfo' => '/LoggerLocationInfo.php',
		'com\extremeidea\php\tools\log4php\LoggerLoggingEvent' => '/LoggerLoggingEvent.php',
		'com\extremeidea\php\tools\log4php\LoggerMDC' => '/LoggerMDC.php',
		'com\extremeidea\php\tools\log4php\LoggerNDC' => '/LoggerNDC.php',
		'com\extremeidea\php\tools\log4php\LoggerLayout' => '/LoggerLayout.php',
		'com\extremeidea\php\tools\log4php\LoggerReflectionUtils' => '/LoggerReflectionUtils.php',
		'com\extremeidea\php\tools\log4php\LoggerRoot' => '/LoggerRoot.php',
		'com\extremeidea\php\tools\log4php\LoggerThrowableInformation' => '/LoggerThrowableInformation.php',
		
		// Appenders
		'com\extremeidea\php\tools\log4php\LoggerAppenderConsole' => '/appenders/LoggerAppenderConsole.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderDailyFile' => '/appenders/LoggerAppenderDailyFile.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderEcho' => '/appenders/LoggerAppenderEcho.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderFile' => '/appenders/LoggerAppenderFile.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderMail' => '/appenders/LoggerAppenderMail.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderMailEvent' => '/appenders/LoggerAppenderMailEvent.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderMongoDB' => '/appenders/LoggerAppenderMongoDB.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderNull' => '/appenders/LoggerAppenderNull.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderFirePHP' => '/appenders/LoggerAppenderFirePHP.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderPDO' => '/appenders/LoggerAppenderPDO.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderPhp' => '/appenders/LoggerAppenderPhp.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderRollingFile' => '/appenders/LoggerAppenderRollingFile.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderSocket' => '/appenders/LoggerAppenderSocket.php',
		'com\extremeidea\php\tools\log4php\LoggerAppenderSyslog' => '/appenders/LoggerAppenderSyslog.php',
		
		// Configurators
		'com\extremeidea\php\tools\log4php\LoggerConfigurationAdapter' => '/configurators/LoggerConfigurationAdapter.php',
		'com\extremeidea\php\tools\log4php\LoggerConfigurationAdapterINI' => '/configurators/LoggerConfigurationAdapterINI.php',
		'com\extremeidea\php\tools\log4php\LoggerConfigurationAdapterPHP' => '/configurators/LoggerConfigurationAdapterPHP.php',
		'com\extremeidea\php\tools\log4php\LoggerConfigurationAdapterXML' => '/configurators/LoggerConfigurationAdapterXML.php',
		'com\extremeidea\php\tools\log4php\LoggerConfiguratorDefault' => '/configurators/LoggerConfiguratorDefault.php',

		// Filters
		'com\extremeidea\php\tools\log4php\LoggerFilterDenyAll' => '/filters/LoggerFilterDenyAll.php',
		'com\extremeidea\php\tools\log4php\LoggerFilterLevelMatch' => '/filters/LoggerFilterLevelMatch.php',
		'com\extremeidea\php\tools\log4php\LoggerFilterLevelRange' => '/filters/LoggerFilterLevelRange.php',
		'com\extremeidea\php\tools\log4php\LoggerFilterStringMatch' => '/filters/LoggerFilterStringMatch.php',

		// Helpers
		'com\extremeidea\php\tools\log4php\LoggerFormattingInfo' => '/helpers/LoggerFormattingInfo.php',
		'com\extremeidea\php\tools\log4php\LoggerOptionConverter' => '/helpers/LoggerOptionConverter.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternParser' => '/helpers/LoggerPatternParser.php',
		'com\extremeidea\php\tools\log4php\LoggerUtils' => '/helpers/LoggerUtils.php',
	
		// Pattern converters
		'com\extremeidea\php\tools\log4php\LoggerPatternConverter' => '/pattern/LoggerPatternConverter.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterClass' => '/pattern/LoggerPatternConverterClass.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterCookie' => '/pattern/LoggerPatternConverterCookie.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterDate' => '/pattern/LoggerPatternConverterDate.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterEnvironment' => '/pattern/LoggerPatternConverterEnvironment.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterFile' => '/pattern/LoggerPatternConverterFile.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterLevel' => '/pattern/LoggerPatternConverterLevel.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterLine' => '/pattern/LoggerPatternConverterLine.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterLiteral' => '/pattern/LoggerPatternConverterLiteral.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterLocation' => '/pattern/LoggerPatternConverterLocation.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterLogger' => '/pattern/LoggerPatternConverterLogger.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterMDC' => '/pattern/LoggerPatternConverterMDC.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterMessage' => '/pattern/LoggerPatternConverterMessage.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterMethod' => '/pattern/LoggerPatternConverterMethod.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterNDC' => '/pattern/LoggerPatternConverterNDC.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterNewLine' => '/pattern/LoggerPatternConverterNewLine.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterProcess' => '/pattern/LoggerPatternConverterProcess.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterRelative' => '/pattern/LoggerPatternConverterRelative.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterRequest' => '/pattern/LoggerPatternConverterRequest.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterServer' => '/pattern/LoggerPatternConverterServer.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterSession' => '/pattern/LoggerPatternConverterSession.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterSessionID' => '/pattern/LoggerPatternConverterSessionID.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterSuperglobal' => '/pattern/LoggerPatternConverterSuperglobal.php',
		'com\extremeidea\php\tools\log4php\LoggerPatternConverterThrowable' => '/pattern/LoggerPatternConverterThrowable.php',
		
		// Layouts
		'com\extremeidea\php\tools\log4php\LoggerLayoutHtml' => '/layouts/LoggerLayoutHtml.php',
		'com\extremeidea\php\tools\log4php\LoggerLayoutPattern' => '/layouts/LoggerLayoutPattern.php',
		'com\extremeidea\php\tools\log4php\LoggerLayoutSerialized' => '/layouts/LoggerLayoutSerialized.php',
		'com\extremeidea\php\tools\log4php\LoggerLayoutSimple' => '/layouts/LoggerLayoutSimple.php',
		'com\extremeidea\php\tools\log4php\LoggerLayoutTTCC' => '/layouts/LoggerLayoutTTCC.php',
		'com\extremeidea\php\tools\log4php\LoggerLayoutXml' => '/layouts/LoggerLayoutXml.php',
		
		// Renderers
		'com\extremeidea\php\tools\log4php\LoggerRendererDefault' => '/renderers/LoggerRendererDefault.php',
		'com\extremeidea\php\tools\log4php\LoggerRendererException' => '/renderers/LoggerRendererException.php',
		'com\extremeidea\php\tools\log4php\LoggerRendererMap' => '/renderers/LoggerRendererMap.php',
		'com\extremeidea\php\tools\log4php\LoggerRenderer' => '/renderers/LoggerRenderer.php',
	);
	
	/**
	 * Loads a class.
	 * @param string $className The name of the class to load.
	 */
	public static function autoload($className) {
		if(isset(self::$classes[$className])) {
			include dirname(__FILE__) . self::$classes[$className];
		}
	}
}
