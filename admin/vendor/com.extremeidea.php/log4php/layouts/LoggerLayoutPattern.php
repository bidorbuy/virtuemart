<?php
namespace com\extremeidea\php\tools\log4php; 
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package log4php
 */

/**
 * A flexible layout configurable with a pattern string.
 * 
 * Configurable parameters:
 * 
 * * converionPattern - A string which controls the formatting of logging 
 *   events. See docs for full specification.
 * 
 * @package log4php
 * @subpackage layouts
 * @version $Revision: 170 $
 */
class LoggerLayoutPattern extends LoggerLayout {
	
	/** Default conversion pattern */
	const DEFAULT_CONVERSION_PATTERN = '%date %-5level %logger %message%newline';

	/** Default conversion TTCC Pattern */
	const TTCC_CONVERSION_PATTERN = '%d [%t] %p %c %x - %m%n';

	/** The conversion pattern. */ 
	protected $pattern = self::DEFAULT_CONVERSION_PATTERN;
	
	/** Maps conversion keywords to the relevant converter (default implementation). */
	protected static $defaultConverterMap = array(
		'c' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLogger',
		'lo' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLogger',
		'logger' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLogger',
		
		'C' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterClass',
		'class' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterClass',
		
		'cookie' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterCookie',
		
		'd' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterDate',
		'date' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterDate',
		
		'e' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterEnvironment',
		'env' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterEnvironment',
		
		'ex' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterThrowable',
		'exception' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterThrowable',
		'throwable' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterThrowable',
		
		'F' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterFile',
		'file' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterFile',
			
		'l' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLocation',
		'location' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLocation',
		
		'L' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLine',
		'line' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLine',
		
		'm' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMessage',
		'msg' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMessage',
		'message' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMessage',
		
		'M' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMethod',
		'method' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMethod',
		
		'n' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterNewLine',
		'newline' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterNewLine',
		
		'p' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLevel',
		'le' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLevel',
		'level' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterLevel',
	
		'r' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterRelative',
		'relative' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterRelative',
		
		'req' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterRequest',
		'request' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterRequest',
		
		's' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterServer',
		'server' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterServer',
		
		'ses' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterSession',
		'session' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterSession',
		
		'sid' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterSessionID',
		'sessionid' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterSessionID',
	
		't' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterProcess',
		'pid' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterProcess',
		'process' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterProcess',
		
		'x' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterNDC',
		'ndc' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterNDC',
			
		'X' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMDC',
		'mdc' => '\com\extremeidea\php\tools\log4php\LoggerPatternConverterMDC',
	);

	/** Maps conversion keywords to the relevant converter. */
	protected $converterMap = array();
	
	/** 
	 * Head of a chain of Converters.
	 * @var LoggerPatternConverter 
	 */
	private $head;

	/** Returns the default converter map. */
	public static function getDefaultConverterMap() {
		return self::$defaultConverterMap;
	}
	
	/** Constructor. Initializes the converter map. */
	public function __construct() {
		$this->converterMap = self::$defaultConverterMap;
	}
	
	/**
	 * Sets the conversionPattern option. This is the string which
	 * controls formatting and consists of a mix of literal content and
	 * conversion specifiers.
	 * @param array $conversionPattern
	 */
	public function setConversionPattern($conversionPattern) {
		$this->pattern = $conversionPattern;
	}
	
	/**
	 * Processes the conversion pattern and creates a corresponding chain of 
	 * pattern converters which will be used to format logging events. 
	 */
	public function activateOptions() {
		if (!isset($this->pattern)) {
			throw new LoggerException("Mandatory parameter 'conversionPattern' is not set.");
		}
		
		$parser = new LoggerPatternParser($this->pattern, $this->converterMap);
		$this->head = $parser->parse();
	}
	
	/**
	 * Produces a formatted string as specified by the conversion pattern.
	 *
	 * @param LoggerLoggingEvent $event
	 * @return string
	 */
	public function format(LoggerLoggingEvent $event) {
		$sbuf = '';
		$converter = $this->head;
		while ($converter !== null) {
			$converter->format($sbuf, $event);
			$converter = $converter->next;
		}
		return $sbuf;
	}
}