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
 * Class Core
 * @package com\extremeidea\bidorbuy\storeintegrator\core
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class Core {

    const EIGAKEY = 'VUEtMjUwOTQyMDgtMg==';
    /**
     * Settings object
     *
     * @var Settings $settings contains settings
     */
    protected $settings;

    /**
     * Logger object
     *
     * @var Logger $logger log events
     */
    private $logger;

    /**
     * Version object
     *
     * @var Version $version plugin and platform version info
     */
    protected $version;

    /**
     * Tradefeed object
     *
     * @var Tradefeed $tradefeed operations with XML file
     */
    protected $tradefeed;

    /**
     * Request object
     *
     * @var http\Request $request wrapped in class super-globals
     */
    public $request;

    /**
     * Queries object
     *
     * @var Queries $queries contains queries to DB
     */
    private $queries = null;

    /**
     * Database resource
     *
     * @var Db $dbLink link to database
     */
    protected $dbLink = null;

    /**
     * Get queries object
     *
     * @return Queries instance of Queries
     */
    public function getQueries() {
        return $this->queries;
    }

    /**
     * @var string $guid
     */
    private $guid = '';

    /**
     * Time when export is started
     *
     * @var integer $timeStart time
     */
    private $timeStart;

    /**
     * Max export time
     *
     * @var integer $timeLimit max time
     */
    private $timeLimit = 7200; //almost 2 hours

    /**
     * Count of products per iteration
     *
     * @var integer $itemsPerIteration count of products
     */
    private $itemsPerIteration = 500;

    /**
     * Core constructor.
     *
     * @return void
     */
    public function __construct() {
        $this->request = new http\Request();

        $this->timeStart = $this->request->server->get('REQUEST_TIME') ?: time();

        $this->settings = new Settings();
        $this->logger = new Logger($this->settings);

        $this->tradefeed = new Tradefeed();
        $this->version = new Version();

        $this->guid = uniqid();
        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * Function for execution on shutdown
     *
     * @return void
     */
    public function shutdown() {
        $lastError = error_get_last();
        if (!is_null($lastError)) {
            $this->logFatal('
            Fatal... type: ' . $lastError['type'] . ', message: ' . $lastError['message']
                . ', file: ' . $lastError['file'] . ', line: ' . $lastError['line']
            );
        }
    }

    /**
     * Initialize core
     *
     * @param string $storeName store name
     * @param string $storeEmail admin email
     * @param string $platform CMS platform
     * @param string $settings serialized settings stored in DB
     * @param array $dbSettings array with settings
     *
     * @return void
     */
    public function init($storeName, $storeEmail, $platform,
                         $settings = null, $dbSettings = null) {
        Settings::$storeName = strval($storeName);
        Settings::$storeEmail = strval($storeEmail);

        Version::$platform = strval($platform);

        if ($dbSettings) {
            $this->queries = new Queries($dbSettings[Db::SETTING_PREFIX], $this->tradefeed);
            $this->dbLink = $this->getDbLinkInstance(
                $dbSettings[Db::SETTING_SERVER], $dbSettings[Db::SETTING_USER],
                $dbSettings[Db::SETTING_PASS], $dbSettings[Db::SETTING_DBNAME]
            );
        }

        if (!is_null($settings) && is_string($settings) && !empty($settings)) {
            $this->settings->unserialize($settings, true);
        }
    }

    /**
     * Get settings Instance
     *
     * @return Settings
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Get current time limit execution
     *
     * @return int
     */
    public function getTimeLimit() {
        return $this->timeLimit;
    }

    /**
     * Hash data from array
     *
     * @param array $data array with data
     * @return string
     */
    public function shash($data = array()) {
        $hash = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $hash[] = $k . '::' . (empty($v) ?
                        '[^:]+' : str_replace('/', '\/', $v));
            }
        }

        return implode('##', $hash);
    }

    /**
     * Validate request by token for download function, Refresh token
     *
     * @param string $token token from GET request
     * @return bool
     */
    public function canTokenDownload($token) {
        $token = trim($token);
        return !empty($token) && $token == $this->settings->getTokenDownload();
    }

    /**
     * Validate request by token for export function
     *
     * @param string $token token from GET request
     * @return bool
     */
    public function canTokenExport($token) {
        $token = trim($token);
        return !empty($token) && $token == $this->settings->getTokenExport();
    }

    /**
     * Choose action for Log section from request
     *
     * @param string $action action(download, remove) logs
     * @param array $data contains log(s) file name
     * @return array
     */
    public function processAction($action, $data = array()) {

        if (isset($data[Settings::nameLoggingFormFilename])) {

            if ($action == Settings::nameLoggingFormActionDownload) {
                $this->downloadLogs($data[Settings::nameLoggingFormFilename]);
            }

            if ($action == Settings::nameLoggingFormActionRemove) {
                $this->deleteLogs($data[Settings::nameLoggingFormFilename]);

                return array(
                    empty($data[Settings::nameLoggingFormFilename]) ?
                        'Log files successfully removed' : 'Log file ' . strip_tags($data[Settings::nameLoggingFormFilename]) . ' successfully removed!'
                );
            }

        }

        if ($action == Settings::nameActionReset) {
            $this->settings->setTokenDownload($this->settings->generateToken());
            $this->settings->setTokenExport($this->settings->generateToken());
        }

        return array();
    }

    public function onRequestComplete(\MultiRequest_Request $request,
                                      \MultiRequest_Handler $handler) {
        $this->info2browser('', '<br/><br/>');
        $this->info2browser($request->getUrl());
        if ($request->getPostData()) {
            $this->info2browser(json_encode($request->getPostData(), true));
        }
        $this->info2browser('Response Code: ' . $request->getCode());

        $this->info2browser('Response Content:');
        $this->info2browser($request->getContent());

        if ($request->getCode() != 200) {
            $this->logError('Export with guid: ' . $this->guid
                . ' failed on http error code: ' . $request->getCode());

            $this->error2browser('Response Exception:');
            $this->error2browser($request->getFailException()->getMessage());
        }

        if ($handler->getActiveRequestsCount() == 0) {
            echo '<br/>' . 'Export is completed.';
        }

        flush();
    }

    public function export($token, $exportConfiguration = array()) {

        if (!$this->canTokenExport($token)) {
            $this->show403Token($token);
        }

        $this->startLogSection($token, $this->gcEnable());

        if (!$this->validateExportRequest($exportConfiguration)) {
            $this->callExit();
        }

        if (isset($exportConfiguration[Settings::paramExtensions]) && is_array($exportConfiguration[Settings::paramExtensions])) {
            $this->logInfo('Extensions: ' . implode('; ', array_values($exportConfiguration[Settings::paramExtensions])));
        }

        if (isset($exportConfiguration[Settings::paramCategoryId])) {
            $exportConfiguration[Settings::paramCategories] = array();
            $exportConfiguration[Settings::paramCategories][] = $exportConfiguration[Settings::paramCategoryId];
        }

        $categories = $exportConfiguration[Settings::paramCategories];

        $this->logInfo('Processing category ids: ' . implode(', ', $categories));
        $timeStart = time();

        $this->dbLink ?
            $this->exportFromNormalizedPlatforms($timeStart, $exportConfiguration) :
            $this->exportFromUnnormalizedPlatforms($exportConfiguration);

        $timeFinish = time();
        $this->logInfo('Total export time is ' . ($timeFinish - $timeStart) . ' s');
        $this->memory2browser();

        $this->callExit();
    }


    /**
     * Export helper functions /START/
     */

    private function exportFromUnnormalizedPlatforms($exportConfiguration) {

        if (isset($exportConfiguration[Settings::paramIds]) && $exportConfiguration[Settings::paramIds]) {
            $this->exportProductsInFile($exportConfiguration);
            return;
        }

        $this->ssga($this->getCurrentServerUrl());
        $this->deleteCategoryOutputFiles();

        $pids = call_user_func($exportConfiguration[Settings::paramCallbackGetProducts]);
        $productsIterations = array_chunk($pids, $this->itemsPerIteration);

        $urls = array();
        $baseUrl = $this->getCurrentServerUrl();

        if (empty($pids)) {
            $this->info2browser('Nothing to export.');
            return;
        }

        for ($i = count($productsIterations) - 1; $i >= 0; $i--) {
            $urls[] = array(
                'baseUrl' => $baseUrl,
                Settings::paramIds => implode(',', $productsIterations[$i])
            );
        }

        $this->launchMultiRequests($urls);
    }

    private function exportFromNormalizedPlatforms($timeStart, $exportConfiguration) {

        if (isset($exportConfiguration[Settings::paramIds]) && $exportConfiguration[Settings::paramIds]) {
            $timeStart = isset($exportConfiguration[Settings::paramTimeStart]);

            if ($timeStart) {
                $this->timeStart = $exportConfiguration[Settings::paramTimeStart];
            }

            $this->exportProducts($exportConfiguration[Settings::paramIds], $exportConfiguration);

            return;
        }

        $statuses = array(Queries::STATUS_NEW, Queries::STATUS_UPDATE);

        $this->ssga($this->getCurrentServerUrl());

        foreach ($this->queries->getClearJobsQueries($this->timeStart) as $q) {
            $this->dbLink->execute($q);
        }

        $this->logInfo('Getting jobs.');
        $jobs = $this->getJobs();

        $jobsKeys = array_keys($jobs);

        foreach ($jobsKeys as $key) {
            foreach ($jobs[$key] as $k => $j) {
                $jobs[$key][$k] = array_shift($j);
            }
        }

        //if product was found with "D" mark - it can't be created or
        // updated any more after deleting. Such products are certainly deleted.
        $jobs[Queries::STATUS_NEW] = array_diff($jobs[Queries::STATUS_NEW], $jobs[Queries::STATUS_DELETE]);

        $jobs[Queries::STATUS_UPDATE] = array_unique(array_diff($jobs[Queries::STATUS_UPDATE], $jobs[Queries::STATUS_DELETE]));

        //if product was found with "U" and "N" marks, we should left it only in one queue to avoid double processing
        $jobs[Queries::STATUS_NEW] = array_unique(array_diff($jobs[Queries::STATUS_NEW], $jobs[Queries::STATUS_UPDATE]));

        $this->logInfo('Products quantity for exporting: ' . count($jobs, COUNT_RECURSIVE));

        //mark DEL jobs as processing
        if (!empty($jobs[Queries::STATUS_DELETE])) {
            $this->dbLink->execute($this->queries->getSetJobsRowStatusQuery(implode(',', $jobs[Queries::STATUS_DELETE]),
                Queries::STATUS_PROCESSING, $this->timeStart));
        }

        //Delete products first. It has to be fast.
        foreach ($jobs[Queries::STATUS_DELETE] as $idsPaginated) {
            $this->removeProducts($idsPaginated, $exportConfiguration);
        }

        //mark DEL jobs as finished
        if (!empty($jobs[Queries::STATUS_DELETE])) {
            $this->dbLink->execute(
                $this->queries->getSetJobsRowStatusQuery(
                    implode(',', $jobs[Queries::STATUS_DELETE]),
                    Queries::STATUS_DELETE,
                    $this->timeStart
                )
            );
        }

        unset($jobs[Queries::STATUS_DELETE]);

        $jobs[Queries::STATUS_NEW] = array_chunk($jobs[Queries::STATUS_NEW], $this->itemsPerIteration);

        $jobs[Queries::STATUS_UPDATE] = array_chunk($jobs[Queries::STATUS_UPDATE], $this->itemsPerIteration);

        foreach ($statuses as $statusValue) {
            foreach ($jobs[$statusValue] as $k => $c) {
                $jobs[$statusValue][$k] = join(',', $c);
            }
        }

        $timeFinish = time();
        $this->logInfo('Tradefeed URLs generation time is ' . ($timeFinish - $timeStart) . ' s');

        if (empty($jobs[Queries::STATUS_NEW]) && empty($jobs[Queries::STATUS_UPDATE])) {
            $this->info2browser('Nothing to export.');
            return;
        }

        $this->launchMultiRequests($jobs);
    }

    /**
     * @param $token
     */
    private function startLogSection($token, $gc_enabled) {
        $this->logInfo('Version is: ' . $this->version->getLivePluginVersion());
        $this->logInfo('Export with settings: ' . $this->settings->serialize());
        $this->logInfo('Export token: ' . $token);
        $this->logInfo('Metrics: ' . json_encode($this->version->getMetrics()));
        $this->logInfo('Platform: ' . Version::$platform);
        $this->logInfo('Version: ' . Version::$version);
        $this->logInfo('Core version: ' . Version::$coreVersion);
        $this->logInfo('Trying to set gc_enable.');
        $this->logInfo('gc_enabled: ' . $gc_enabled);
        $this->syncTimeLimit();
    }

    private function gcEnable() {
        if (function_exists('gc_enable')) {
            @gc_enable();
        }
        return gc_enabled();
    }

    private function validateExportRequest($exportConfiguration) {

        $warnings = $this->getWarnings();

        $validateRules = array(
            array(
                'result' => empty(Version::$platform),
                'message' => 'platform is undefined.'
            ),
            array(
                'result' => !isset($exportConfiguration[Settings::paramCallbackExportProducts]),
                'message' => 'paramCallbackExportProducts is undefined.'
            ),

            array(
                'result' => !isset($exportConfiguration[Settings::paramCallbackGetProducts]),
                'message' => 'paramCallbackGetProducts is undefined.'
            ),

            array(
                'result' => !isset($exportConfiguration[Settings::paramCallbackGetBreadcrumb]),
                'message' => 'paramCallbackGetBreadcrumb is undefined.'
            ),

            array(
                'result' => !isset($exportConfiguration[Settings::paramCategories])
                    || (isset($exportConfiguration[Settings::paramCategories])
                        && !is_array($exportConfiguration[Settings::paramCategories])),
                'messsage' => 'paramCategories is undefined.'
            ),

            array(
                'result' => !$this->request->server->get('REQUEST_URI'),
                'message' => 'export should be called within application web server only.'
            ),

            array(
                'result' => !empty($warnings),
                'message' => 'Errors: ' . implode('. ', $warnings)
            )
        );

        foreach ($validateRules as $validate) {
            if ($validate['result']) {
                $this->error2browser($validate['message']);
                return false;
            }
        }
        return true;
    }

    /**
     * Export helper functions /END/
     */

    public function download($token, $exportConfiguration = array()) {
        $this->timeLimit = 0;
        $this->syncTimeLimit();

        $this->logInfo('Download with settings: ' . $this->settings->serialize());
        $this->logInfo('Download token: ' . $token);
        $this->ssga($this->getCurrentServerUrl());

        if (!$this->canTokenDownload($token)) {
            $this->show403Token($token);
        }

        $this->dbLink == false ?
            $this->unnormalizedPlatformsBuildFile() :
            $this->normalizedPlatformsBuildFile($exportConfiguration);

        $this->compressOutput();
        $compressLibrary = $this->settings->getCompressLibrary();
        $options = $this->settings->getCompressLibraryOptions();

        $fileCompress = $this->settings->getCompressOutputFile();
        $errorMessage = '
                        \nTradefeed has not been generated.
                        \n At first generate tradefeed and then download.
                        ';
        $this->downloadFileInternally(
            $fileCompress,
            $options[$compressLibrary]['mime-type'],
            false,
            $this->settings->cleanProtectedExtension($fileCompress),
            $errorMessage
        );

        $this->callExit();
    }

    private function normalizedPlatformsBuildFile($exportConfiguration) {

        if (!isset($exportConfiguration[Settings::paramCategories])) {
            $this->error2browser('paramCategories is undefined.');
            $this->callExit();
        }

        $file = $this->settings->getOutputFile();

        $resource = fopen($file, 'w');
        fwrite($resource, $this->tradefeed->createStartRootTag());
        fwrite($resource, $this->tradefeed->createVersionSection());

        fwrite($resource, $this->tradefeed->createStartProductsTag());

        $pagination = 100000; // implement pagination to prevent database disconnects
        $count = $this->dbLink->executeS($this->queries->getXmlViewDataCountQuery(), true);
        $count = ceil($count[0][Queries::ROW_NAME] / $pagination);

        for ($i = 0; $i < $count; $i++) {
            $result = $this->dbLink->query($this->getQueries()
                    ->getXmlViewDataQuery(
                        $exportConfiguration[Settings::paramCategories]) . ' LIMIT ' .
                $i * $pagination . ', ' . $pagination, true
            );

            while ($row = $this->dbLink->nextRow($result)) {
                fwrite($resource, $row[Queries::ROW_NAME]);
            }
        }

        fwrite($resource, $this->tradefeed->createEndProductsTag());
        fwrite($resource, $this->tradefeed->createEndRootTag());
        fclose($resource);

        $this->logInfo('XML File Generation Time: ' .
            strval(time() - $this->timeStart) . " seconds.");
    }

    private function unnormalizedPlatformsBuildFile() {

        $files = $this->scanForCategoryOutputFiles('md5');
        if (!empty($files)) {
            $file = $this->settings->getOutputFile();
            $resource = fopen($file, 'w');

            fwrite($resource, $this->tradefeed->createStartRootTag());
            fwrite($resource, $this->tradefeed->createVersionSection());

            fwrite($resource, $this->tradefeed->createStartProductsTag());

            foreach ($files as $file) {
                $f = Settings::$dataPath . '/' . $file['filename'];
                if (filesize($f) > 0) {

                    if ($fp = @fopen($f, 'r')) {
                        while (!feof($fp)) {
                            fwrite($resource, fread($fp, filesize($f)));
                        }

                        if (!fclose($fp)) {
                            $this->logError('Unable to close source file: ' . $f);
                        }
                    }

                    if (!$fp) {
                        $this->logError('Unable to open ' . $f);
                    }
                }

                @unlink($f);
            }
            fwrite($resource, $this->tradefeed->createEndProductsTag());

            fwrite($resource, $this->tradefeed->createEndRootTag());
            fclose($resource);
        }
    }

    public function downloadl($token) {
        $this->ssga($this->getCurrentServerUrl());

        if (!$this->canTokenDownload($token)) {
            $this->show403Token($token);
        }

        $this->downloadLogs();
    }

    public function showVersion($token, $phpinfo) {
        $this->ssga($this->getCurrentServerUrl());

        if (!$this->canTokenDownload($token)) {
            $this->show403Token($token);
        }

        $phpinfo === true ? $this->phpInfo() : $this->jsonMetrics();
        $this->callExit();
    }

    public function resetAudit() {
        echo "
            <html>
            <head>
            <title>Reset export data</title>
            </head>
            <body>
            <h1>Reset export data</h1>
            <br/>All previously exported data was cleared.
            <br/> Next export process will be done from scratch.
            </body>
            </html>
            ";

        $this->callExit();
    }

    /**
     * @return array
     */
    public function getWarnings() {
        $warnings = array();

        foreach (array(Settings::$dataPath, Settings::$logsPath) as $path) {
            if (!is_writable($path)) {
                $warnings[] = 'The required path is not writable: ' . $path;
            }
        }

        $dbExtension = $this->selectMySQLPhpExtension();

        $extensions = array(
            'curl' =>
                '"curl" extension requires to be installed and enabled in PHP.',

            'mbstring' =>
                '"mbstring" extension requires to be installed and enabled in PHP.',

            'libxml' =>
                '"libxml" extension requires to be installed and enabled in PHP.',

            $dbExtension =>
                '"'
                . $dbExtension
                . '" extension requires to be installed and enabled in PHP.'
        );

        foreach ($extensions as $extension => $errorMessage) {
            if (!$this->isExtensionLoaded($extension)) {
                $warnings[] = $errorMessage;
            }
        }

        $disabledFunctions = $this->getPhpDisableFunctions();
        if (isset($disabledFunctions)) {
            $functions = explode(',', $disabledFunctions);
            if (in_array('readfile', $functions)) {
                $warnings[] = 'Required PHP function "readfile" is disabled for security reasons (@see http://php.net/manual/en/ini.core.php#ini.disable-functions). Please contact your system administrator to get this function enabled.';
            }
        }

        if (version_compare($this->getPhpVersion(), '5.3.0') < 0) {
            $warnings[] = 'The minimal PHP version 5.3.0 is required.';
        }

        return $warnings;
    }

    public function getPhpDisableFunctions() {
        return ini_get('disable_functions');
    }

    public function downloadLogs($file = null) {
        $this->logInfo('Download logs with file: ' . $file);

        // We should reset $filename to empty if zip extension is turned off
        // to let browser download empty file instead of error.

        $filename = !is_null($file) && !empty($file) ?
            (Settings::$logsPath . '/' . $file) : '';

        $this->logInfo('Download logs with filename: ' . $filename);

        $destination = is_null($file) || empty($file) ? 'bobsi_logs' : $file;
        $this->logInfo('Download logs with destination: ' . $destination);

        $files = array();
        if (is_null($file) || empty($file)) {
            $logs = $this->scanForLogFiles();

            foreach ($logs as $log) {
                $f = Settings::$logsPath . '/' . $log['filename'];
                $files[] = array('file' => $f, 'basename' => basename($f));
            }
        } else if (file_exists($filename)) {
            $files[] = array('file' => $filename, 'basename' => $file);
        }

        if (array_key_exists('zip', $this->settings->getCompressLibraryOptions())) {
            $destination = Settings::$logsPath . '/' . $destination . '.zip';
            $this->logInfo('Download logs with destination: ' . $destination);

            $success = $this->compress('zip', $destination, $files);

            $message = 'Compress logs is finished with status: ' . $success . ' : ';
            file_exists($destination) ?
                $this->logInfo($message . 'File exists.') : $this->logError($message . 'File does not exists.');

            if ($success && file_exists($destination)) {
                $options = $this->settings->getCompressLibraryOptions();

                $this->downloadFileInternally(
                    $destination,
                    $options['zip']['mime-type'],
                    true
                );
            }

            $this->callExit();
        }

        $this->downloadFileInternally($filename, 'text/plain', true);

        $this->callExit();
    }

    public function deleteLogs($file = null) {
        $filename = !is_null($file) ? Settings::$logsPath . '/' . $file : '';

        if (is_null($file) || empty($file)) {
            $files = $this->scanForLogFiles();
            foreach ($files as $file) {
                @unlink(Settings::$logsPath . '/' . $file['filename']); // Today's log file may be blocked and undeletable.
            }
        } else if (file_exists($filename)) {
            @unlink($filename);
        }
    }

    /**
     * @return string
     */
    public function getLogsHtml() {
        $html = $this->getLogsListHtml();

        $zipDownloadHtml = '';
        if (array_key_exists('zip', $this->settings->getCompressLibraryOptions())) {
            $zipDownloadHtml = '
                <button type="button" class="button '
                . Settings::nameLoggingFormButton . '" filename=""
                action="' . Settings::nameLoggingFormActionDownload
                . '">Download all</button>';
        }

        return '<form name="' . Settings::nameLoggingForm . '"
                      id="' . Settings::nameLoggingForm . '"
                      method="POST" action="">
                    <input type="hidden" name="' . Settings::nameLoggingFormAction . '"
                           id="' . Settings::nameLoggingFormAction . '" value="" />
                    <input type="hidden" name="' . Settings::nameLoggingFormFilename . '"
                    id="' . Settings::nameLoggingFormFilename . '" value="" />
                    <table class="bobsi-logging-form-table">
                    <tr><td colspan="2"> ' . $zipDownloadHtml . '
                            <button type="button"
                                class="button ' . Settings::nameLoggingFormButton . '" filename=""
                                action="' . Settings::nameLoggingFormActionRemove . '">Remove all</button></td></tr>
                    ' . $html . '
                    </table>
                </form>
                <div id="ctrl-c-message">Press Ctrl+C</div>';
    }

    public function show403Token($token) {
        $this->logError('Unable to authenticate token: ' . $token);

        header('HTTP/1.0 403 Forbidden');
        echo "<html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1>
        Unable to authenticate token. Please check the link.</body></html>";

        $this->callExit();
    }

    /**
     * @param string $productIds separated by comma
     * @param array $exportConfiguration
     */
    private function exportProducts($productIds, $exportConfiguration = array()) {
        $this->memory2browser();
        $timeStart = time();
        $productIdsArray = explode(',', $productIds);

        $this->logInfo(count($productIdsArray) . ' products came to process: ' . $productIds);

        $untouchedProductsArray = $this->dbLink->executeS(
            $this->queries->getSelectUntouchedProducts($productIds), true
        );

        foreach ($untouchedProductsArray as $k => $v) {
            $untouchedProductsArray[$k] = array_shift($v);
        }
        $untouchedProductsString = join(',', $untouchedProductsArray);

        $this->logInfo('Processing ' . count($untouchedProductsArray) . ' products of ' . count($productIdsArray) . ': ' . $untouchedProductsString);

        //mark jobs as processing
        $this->dbLink->execute($this->queries->getSetJobsRowStatusQuery($untouchedProductsString, Queries::STATUS_PROCESSING, $this->timeStart));

        //remove old products from tradefeed on update
        if (!$exportConfiguration[Settings::paramProductStatus] OR $exportConfiguration[Settings::paramProductStatus] == Queries::STATUS_UPDATE) {
            $this->removeProducts($untouchedProductsString, $exportConfiguration);
        }

        $exportedProductsCounter = 0;

        time_nanosleep(1, 10000000);

        foreach ($untouchedProductsArray as $pid) {
            $products = call_user_func($exportConfiguration[Settings::paramCallbackExportProducts], $r = &$pid, $r = &$exportConfiguration);

            if ($products && is_array($products) && !empty($products)) {
                $productsData = array();
                $keys = array_keys($this->buildArrayToInsert($productsData, false));

                $fullDescription = $this->tradefeed->swapSummaryDescription(
                    $products[Tradefeed::nameProductSummary],
                    $products[Tradefeed::nameProductDescription]
                );
                /**
                 * Add base url to images in description
                 * Platforms: Joomla.
                 * @baseUrl contains on each variation,
                 * by default get from first variation/
                 *
                 */
                $baseURL = isset($products[0][Tradefeed::nameBaseUrl]) ?
                    $products[0][Tradefeed::nameBaseUrl] : '';

                if (!empty($baseURL)) {
                    $products[Tradefeed::nameProductDescription] =
                        preg_replace(
                            '#(<img.*src=")([^http].*")(.*>)#isU',
                            "$1$baseURL/$2$3",
                            $products[Tradefeed::nameProductDescription]
                        );
                }
                /**
                 *  Add full description to product array, for parse product description
                 *  and get images from description
                 *  count() - 3 because:
                 *  Product = Array(
                 *       Summary     => string, (-1)
                 *       Description => string  (-1)
                 *       Product     => array() (-1, iteration start from zero)
                 */
                for ($i = 0; $i <= count($products) - 3; $i++) {
                    $products[$i][Tradefeed::nameProductDescription] = $fullDescription;
                }

                /*
                 * Feature 3750
                 * New conditions are added to Feature 3750, depending on the switch,
                 * where the user selects that need to export to a XML file
                 */
                $productDataDescription = $this->buildArrayToInsert($products, true);
                $this->dbLink->execute($this->queries->getInsertProductDataQuery($pid, $productDataDescription));
                /*
                 * End Feature
                 */
                unset($products[Tradefeed::nameProductSummary]);
                unset($products[Tradefeed::nameProductDescription]);

                for ($j = count($products) - 1; $j >= 0; $j--) {
                    $product = &$products[$j];
                    if ($j % 10 == 0) {
                        time_nanosleep(0, 10000000);
                    }

                    $product = $this->tradefeed->prepareProductArray($product, $exportConfiguration);
                    $productsData[] = $this->buildArrayToInsert($product, false);
                    $products[$j] = null;
                    $product = null;
                }

                $this->dbLink->execute($this->queries->getBulkInsertProductQuery($keys, $productsData));

                $exportedProductsCounter += count($products);

                $this->logInfo('Saved ' . count($products) . ' products to tradefeed');
                $this->memory2browser();
            }
            $products = null;
        }

        //mark jobs as finished
        $this->dbLink->execute(
            $this->queries->getSetJobsRowStatusQuery(
                $untouchedProductsString,
                Queries::STATUS_DELETE,
                $this->timeStart
            )
        );

        $timeFinish = time();
        $this->logInfo('Processing product time is ' . ($timeFinish - $timeStart) . ' s');
        $this->info2browser('Total: ' . $exportedProductsCounter . ' combinations have been exported to tradefeed.');
        $this->memory2browser();

        $this->callExit();
    }

    private function exportProductsInFile($exportConfiguration) {
        $this->memory2browser();

        $timeStart = time();

        $pids = explode(',', $exportConfiguration[Settings::paramIds]);

        $file = $this->settings->getCategoryTemporaryOutputFile(md5($exportConfiguration[Settings::paramIds]));
        $resource = fopen($file, 'w');

        $this->logInfo('Processing products ids: ' . $exportConfiguration[Settings::paramIds]);

        $exportedProductsCounter = 0;

        $products = call_user_func($exportConfiguration[Settings::paramCallbackExportProducts], $param = &$pids, $param = &$exportConfiguration);
        if ($products && is_array($products) && !empty($products)) {

            $iteration = 1;
            foreach ($products as &$product) {

                //http://stackoverflow.com/questions/584960/whats-better-at-freeing-memory-with-php-unset-or-var-null/13461577#13461577
                if ($iteration % 10 == 0) {
                    time_nanosleep(0, 10000000);
                }

                fwrite($resource, $this->tradefeed->createProductSection($product, $exportConfiguration));
                fflush($resource);

                $product = null;
                $iteration++;
            }
            unset($product);
            $exportedProductsCounter += count($products);

            $this->logInfo('Saved ' . count($products) . ' products to tradefeed');
            $this->memory2browser();
        }
        $products = null;

        fclose($resource);

        $status = @rename($file, $this->settings->getCategoryOutputFile(md5($exportConfiguration[Settings::paramIds])));
        if (!$status) {
            $this->logError('Unable to rename file from ' . basename($file) . ' to ' . basename($this->settings->getCategoryOutputFile(md5($exportConfiguration[Settings::paramIds]))));
        }

        $timeFinish = time();
        $this->logInfo('Export time is ' . ($timeFinish - $timeStart) . ' ms');
        $this->info2browser('Total: ' . $exportedProductsCounter . ' product(s) have been exported to tradefeed.');

        $this->memory2browser();

        $this->callExit();
    }

    public function logInfo($message, $data = '') {
        $this->logger->info($this->guid . ': ' . $message . ' ' . $this->joinData($data));
    }

    public function logError($message) {
        $this->logger->error($this->guid . ': ' . $message);
    }

    public function logFatal($message) {
        $this->logger->fatal($this->guid . ': ' . $message);
    }

    private function joinData($data = '') {
        $string = $data;

        if (is_array($data)) {
            $tmpData = array();
            foreach ($data as $k => $v) {
                $tmpData[] = $k . '=' . $this->joinData($v);
            }
            $string = implode(', ', $tmpData);
        }

        return $string;
    }

    private function deleteCategoryOutputFiles($file = null) {
        $filename = !is_null($file) ? Settings::$dataPath . '/' . $file : '';

        if (is_null($file) || empty($file)) {
            $files = $this->scanForCategoryOutputFiles();
            foreach ($files as $file) {
                @unlink(Settings::$dataPath . '/' . $file['filename']);
            }
        } else if (file_exists($filename)) {
            @unlink($filename);
        }
    }

    private function scanForCategoryOutputFiles($type = 'all') {
        $files = scandir(Settings::$dataPath);
        unset($files[0]); // .
        unset($files[1]); // ..

        $pattern = $this->settings->getCategoryOutputFilePattern($type);

        $tradefeeds = array();
        foreach ($files as $file) {
            if (preg_match($pattern, $file) && is_file(Settings::$dataPath . '/' . $file)) {
                $tradefeeds[] = array('filename' => $file, 'filesize' => 0);
            }
        }
        unset($files);

        return $tradefeeds;
    }

    /**
     * @return string
     */
    private function getLogsListHtml() {
        $output = '';

        $files = $this->scanForLogFiles();

        for ($c = count($files) - 1, $i = 1; $c >= 0; $c--, $i++) {
            $file = $files[$i - 1];
            $filename = $file['filename'];
            $output .= '<tr><td>' . $i . '. ' . $filename . ' (' . $file['filesize'] . ')</td>';
            $output .= '<td><button class="button ' . Settings::nameLoggingFormButton . '"
                               action="' . Settings::nameLoggingFormActionDownload . '" type="button" filename="' . $filename . '">Download</button>
                       <button class="button ' . Settings::nameLoggingFormButton . '"
                               action="' . Settings::nameLoggingFormActionRemove . '" type="button" filename="' . $filename . '">Remove</button></td>';
        }

        return $output;
    }

    /**
     * @return array
     */
    private function scanForLogFiles() {
        $files = scandir(Settings::$logsPath);
        unset($files[0]); // .
        unset($files[1]); // ..

        $logs = array();
        foreach ($files as $file) {
            if (preg_match('/^bobsi_\d{4}\-\d{2}\-\d{2}\.log$/i', $file) && is_file(Settings::$logsPath . '/' . $file)) {
                $filesize = $this->formatbytes(Settings::$logsPath . '/' . $file, 'MB');

                $sizeOfFile = filesize(Settings::$logsPath . '/' . $file);

                $filesize = number_format($sizeOfFile / 1024, 2, ',', ' ') . ' KB';
                if ($sizeOfFile > (1024 * 1000)) {
                    $filesize = number_format($sizeOfFile / 1024 / 1024, 2, ',', ' ') . ' MB';
                }
//                $logs[] = array('filename' => $file, 'filesize' => number_format((float)$filesize, 2, ',', ' ') . ' MB');
                $logs[] = array('filename' => $file, 'filesize' => $filesize);
            }
        }
        unset($files);

        return $logs;
    }

    private function compressOutput() {
        $timeStart = time();

        $file = $this->settings->getOutputFile();
        $fileCompress = $this->settings->getCompressOutputFile();

        $status = $this->compress($this->settings->getCompressLibrary(), $fileCompress,
            array(
                array('file' => $file, 'basename' => basename($this->settings->cleanProtectedExtension($file)))
            )
        );

        $timeFinish = time();
        $this->logInfo('Compress time is ' . ($timeFinish - $timeStart) . ' s');
        $this->logInfo('Compressed with status: ' . $status);
    }

    /**
     * Compress files in archive
     *(Logs, tradefeed)
     *
     * @param string $type type of archive(zip, gz)
     * @param string $destination archive name, path
     * @param array $files files to archive
     *
     * @return bool true - OK, false - error
     */
    public function compress($type, $destination, $files = array()) {
        $this->logInfo("Compress with parameters {$type} : {$destination}");

        if (!in_array($type, array_keys($this->settings->getCompressLibraryOptions()))) {
            $this->logError('Unable to detect compress library: ' . $type);
            return false;
        }

        if (!is_array($files)) {
            $this->logError('Unable to cast files variable type to array type.');
            return false;
        }

        $status = true;

        switch ($type) {
            case 'zip':
                $status = $this->compressZip($destination, $files);
                break;
            case 'gzip':
                $status = $this->compressGZip($destination, $files);
                break;
        }

        return $status;
    }

    /**
     * Compress in zip archive
     *
     * @param string $destination archive name and path
     * @param array $files files to archive
     *
     * @return bool true - ok, false - error
     */
    private function compressZip($destination, $files) {
        $zip = new \ZipArchive();

        if (!$zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $this->logError('Unable to create zip destination: ' . $destination);
            return false;
        }

        $errors = 0;

        if (!count($files)) {
            $zip->addFromString('.empty', '');
        }

        foreach ($files as $file) {

            if (!$zip->addFile($file['file'], $file['basename'])) {
                $this->logError('Unable to add file to zip: ' . $file['file']);
                $errors += 1;
            }
        }

        if (!$zip->close()) {
            $this->logError('Unable to close destination file: ' . $destination);
            $errors += 1;
        }
        $this->logInfo('zip is created, count of errors: ' . $errors);

        return true && !$errors;
    }

    /**
     * Compress in Gzip
     *
     * @param string $destination archive name and path
     * @param array $files files to archive
     *
     * @return bool true - ok, false - error
     */
    private function compressGZip($destination, $files) {
        $out = gzopen($destination, 'w9');

        if (!$out) {
            $this->logError('Unable to create gzip destination: ' . $destination);
            return false;
        }

        $errors = 0;

        foreach ($files as $file) {

            $fileToArchive = fopen($file['file'], 'rb');

            if (!$fileToArchive) {
                $this->logError('Unable to open source file: ' . $file['file']);
                $errors += 1;
            }

            while (!feof($fileToArchive)) {
                gzwrite($out, fread($fileToArchive, filesize($file['file'])));
            }

            if (!fclose($fileToArchive)) {
                $this->logError('Unable to close source file: ' . $file['file']);
                $errors += 1;
            }

        }

        if (!gzclose($out)) {
            $this->logError('Unable to close destination file: ' . $destination);
            $errors += 1;
        }

        $this->logInfo('gzip is created, count of errors: ' . $errors);
        return true && !$errors;
    }

    private function downloadFileInternally($file, $contentType, $unlink, $changeFileName = '', $errorMessage = '') {

        if (!file_exists($file)) {
            header('HTTP/1.0 404 Not Found');
            echo "
                    <html>
                    <head>
                        <title>404 Not Found</title>
                    </head>
                    <body>
                        <h1>File not found to download</h1>
                        $errorMessage
                    </body>
                    </html>              
                ";
            $this->callExit();
        }

        $zlib = ini_get("zlib.output_compression");
        $zlib_enabled = intval($zlib) > 0 || strcasecmp($zlib, 'on') == 0;

        $isCsCartWithGzipEnabled =
            defined('AREA')
            && !$zlib_enabled
            && $this->request->server->get('HTTP_ACCEPT_ENCODING')
            && strpos($this->request->server->get('HTTP_ACCEPT_ENCODING'), 'gzip') !== false;

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);

        $fileName = $changeFileName ? basename($changeFileName) : basename($file);
        header('Content-Disposition: attachment; filename="' . $fileName . '";');
        header('Content-Transfer-Encoding: binary');

        if (!$isCsCartWithGzipEnabled) {
            header('Content-Length: ' . $this->getFileSize($file));
        }
        
        /* Defect: #4124 */
        $this->addZlibHeaders($zlib_enabled);
        /*  ***  */

        ini_set("zlib.output_compression", "Off");

        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }

        if (ob_get_contents()) {
            ob_clean();
        }

        if (!$isCsCartWithGzipEnabled) {
//              http://php.net/manual/en/function.readfile.php
//              readfile() will not present any memory issues, even when sending large files, on its own. If you encounter an out of memory error ensure that output buffering is off with ob_get_level().
            if (ob_get_level()) {
                // ob_end_clean Clean (erase) the output buffer and turn off output buffering
                // ob_clean Clean (erase) the output buffer

                // we need to clean buffer and not turning off buffering, if we turn off, in some
                // cases we receive no content as Defect #3695
                ob_clean();
            }
        }

        readfile($file);

        if ($isCsCartWithGzipEnabled) {
//              The specific workaround for cscart, because it uses ob_gzhandler buffer (http://stackoverflow.com/questions/20332598/php-ob-gzhandler-setting-content-length-disables-gzipped-output)
            ob_get_flush(); // Flush the output from ob_gzhandler

//              We need to suppress warning "headers already sent" by @, no other ways available how to get around it ((.
            @header('Content-Length: ' . ob_get_length());
            if (ob_get_level() > 0) {
                ob_end_flush(); // Flush the outer ob_start()
            }
        }

        if ($unlink) {
            @unlink($file);
        }

    }

    private function launchMultiRequests($urls = array()) {

        $mrHandler = new \MultiRequest_Handler();
        $mrHandler->onRequestComplete(array($this, 'onRequestComplete'));
        $mrHandler->setConnectionsLimit(4);

        $headers = array();
        $headers[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Keep-Alive: 300';
        $headers[] = 'Accept-Charset: UTF-8,Windows-1251,ISO-8859-1;q=0.7,*;q=0.7';
        $headers[] = 'Accept-Language: ru,en-us,en;q=0.5';
        $headers[] = 'Pragma:';
        $mrHandler->requestsDefaults()->addHeaders($headers);

        $options = array();
        $options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12';

        $username = $this->settings->getUsername();
        if (!empty($username)) {
            $options[CURLOPT_USERPWD] = $username . ':' . $this->settings->getPassword();
        }
        $mrHandler->requestsDefaults()->addCurlOptions($options);

        $serverName = $this->getServerName();
        $ipaAdress = gethostbyname($serverName);

        $ipaAdress == $serverName ?
            $this->error2browser('Unable to resolve host name: ' . $serverName) :
            $this->info2browser('Resolved host name: ' . $serverName . ' via ' . $ipaAdress);


        if ($this->dbLink) {
            foreach ($urls as $action => $idsArray) {
                foreach ($idsArray as $ids) {
                    $request = new \MultiRequest_Request($this->getCurrentServerUrl());
                    $request->setPostVar(Settings::paramIds, $ids);
                    $request->setPostVar(Settings::paramProductStatus, $action);
                    $mrHandler->pushRequestToQueue($request);
                }
            }
            $mrHandler->start();
            return;
        }

        foreach ($urls as $url) {
            $request = new \MultiRequest_Request($url['baseUrl']);
            foreach ($url as $param => $value) {
                if ($param != 'baseUrl') {
                    $request->setPostVar($param, $value);
                }
            }
            $mrHandler->pushRequestToQueue($request);
        }

        $mrHandler->start();
    }

    private function memory2browser() {
        $this->logInfo('Memory usage: ' . memory_get_usage() . ' bytes');
//        $this->info2browser('Memory usage: ' . memory_get_usage() . ' bytes');
        flush();
    }

    private function info2browser($message, $delimiter = '<br/>') {
        echo $message . $delimiter;
        $this->logInfo($message);
        flush();
    }

    private function error2browser($message, $delimiter = '<br/>') {
        echo $message . $delimiter;
        $this->logError($message);
        flush();
    }

    /**
     * Ecommerce Tracking
     *
     * @param string $url url
     *
     * @return GA object
     */
    private function ssga($url) {
        $ssga = new GA(base64_decode(self::EIGAKEY));
        $ssga->setEvent('bob-' . substr(Version::$platform, 0, strpos(Version::$platform, ' ')), $this->version->getLivePluginVersion(), $url);
        $ssga->send();
        $ssga->reset();
        return $ssga;
    }

    private function formatbytes($file, $type) {
        switch ($type) {
            case "KB":
                $filesize = filesize($file) * .0009765625; // bytes to KB
                break;
            case "MB":
                $filesize = (filesize($file) * .0009765625) * .0009765625; // bytes to MB
                break;
            case "GB":
                $filesize = ((filesize($file) * .0009765625) * .0009765625) * .0009765625; // bytes to GB
                break;
        }

        if ($filesize <= 0) {
            return $filesize = 0;
        }

        return round($filesize, 2) . ' ' . $type;
    }

    /**
     * @return string
     */
    private function getServerName() {
        return $this->request->server->get('HTTP_HOST') ?:
            $this->request->server->get('SERVER_NAME') ?: '';
    }

    public function isHTTPS() {
        $https = $this->request->server->get('HTTPS');
        return $https && $https !== false && strtolower($https) != 'off';
    }

    /**
     * @return string
     */
    public function getCurrentServerUrl() {
        $base = $this->isHTTPS() ? 'https://' : 'http://';

        $host = $this->request->server->get('HTTP_X_FORWARDED_HOST');

        if (!$host) {
            $host = $this->getServerName();
            $port = $this->request->server->get('SERVER_PORT');
            if (!empty($host) && $port) {
                $host .= ':' . $port;
            }
        }

        if (empty($host)) {
            return '';
        }

        $base .= $host;

        // might be the case as host:8080 which was replaced by host80
        if (preg_match('/.*(:80)$/i', $base) == 1) {
            $base = str_replace(':80', '', $base);
        }

        if ($uri = $this->request->server->get('REQUEST_URI')) {
            $base .= html_entity_decode($uri);
        }

        return $base;
    }

    private function syncTimeLimit() {
        $this->logInfo('Trying to set max execution time to ' . $this->timeLimit . ' secs.');
        @set_time_limit($this->timeLimit);
        $currentTimeLimit = ini_get('max_execution_time');
        $this->timeLimit = $currentTimeLimit;
        $this->logInfo('Max execution time is ' . $currentTimeLimit);
    }

    public function getJobs() {
        $jobs[Queries::STATUS_DELETE] = $this->dbLink->executeS(
            $this->queries->getJobsByStatusQuery(Queries::STATUS_DELETE, $this->timeStart), true
        );

        $jobs[Queries::STATUS_NEW] = $this->dbLink->executeS(
            $this->queries->getJobsByStatusQuery(Queries::STATUS_NEW, $this->timeStart), true
        );

        $jobs[Queries::STATUS_UPDATE] = $this->dbLink->executeS(
            $this->queries->getJobsByStatusQuery(Queries::STATUS_UPDATE, $this->timeStart), true
        );

        return $jobs;
    }

    public function getPhpVersion() {
        return $this->version->getPhpVersion();
    }

    public function addParamToUrl($url, $key, $value) {
        // remove ? and & characters from end if any exists.
        $url = preg_replace('/[&?\s]*$/i', '', $url);
        $url = trim($url);

        $url .= strpos($url, '?') > -1 ? '&' : '?';
        return $url . $key . '=' . $value;
    }

    /**
     * Checks if export criteria related settings are changed.
     * @param $oldSettings serialized to string settings. Expects base64 string.
     * @param $newSettings serialized to string settings. Expects base64 string.
     * @return true if export criteria settings are changed, false otherwise.
     */
    public function checkIfExportCriteriaSettingsChanged($oldSettings, $newSettings, $base64) {
        $old = new Settings();
        $new = new Settings();

        $old->unserialize($oldSettings, $base64);
        $new->unserialize($newSettings, $base64);

        return $old->getExportQuantityMoreThan() != $new->getExportQuantityMoreThan()
        || count(array_diff($old->getExcludeCategories(), $new->getExcludeCategories())) > 0
        || count(array_diff($new->getExcludeCategories(), $old->getExcludeCategories())) > 0
        || count(array_diff($old->getExportStatuses(), $new->getExportStatuses())) > 0
        || count(array_diff($new->getExportStatuses(), $old->getExportStatuses())) > 0
        || $old->getDefaultStockQuantity() != $new->getDefaultStockQuantity()
        || $old->getExportProductSummary() != $new->getExportProductSummary()
        || $old->getExportProductDescription() != $new->getExportProductDescription();
    }

    protected function isExtensionLoaded($extension) {
        return extension_loaded($extension);
    }

    protected function selectMySQLPhpExtension() {
        if (version_compare($this->getPhpVersion(), '5.5.0') >= 0) {
            return 'mysqli';
        }

        return $this->isExtensionLoaded('mysqli') ? 'mysqli' : 'mysql';
    }

    protected function getDbLinkInstance($server, $user, $password, $database) {
        static $instance;

        if (!isset($instance)) {
            $class = 'mysqli' == $this->selectMySQLPhpExtension() ? 'com\extremeidea\bidorbuy\storeintegrator\core\MySQLi' : 'com\extremeidea\bidorbuy\storeintegrator\core\MySQL';
            $instance = new $class($server, $user, $password, $database, false);
        }

        return $instance;
    }

    private function removeProducts(&$ids) {
        $this->dbLink->execute($this->queries->getRemoveProductsFromTradefeedQuery($ids, $this->timeStart));
    }

    protected function buildArrayToInsert(array &$data, $includeDescriptionSummaryOnly) {
        $array = array();
        if ($includeDescriptionSummaryOnly === true) {
            $summary = $this->dbLink->escape($data[Tradefeed::nameProductSummary], true);
            $description = $this->dbLink->escape($data[Tradefeed::nameProductDescription], true);
            $productName = isset($data[0][Tradefeed::nameProductName]) ? $this->dbLink->escape($data[0][Tradefeed::nameProductName], true) : '';
            $array['summary'] = ($this->settings->getExportProductSummary() && !empty($summary)) ? $summary : $productName;
            $array['description'] = ($this->settings->getExportProductDescription() && !empty($description)) ? $description : $productName;
            return $array;
        }

        $array['product_id'] = (isset($data[Tradefeed::nameProductId])) ? intval($data[Tradefeed::nameProductId]) : 0;
        $array['variation_id'] = (isset($data[Settings::paramVariationId])) ? intval($data[Settings::paramVariationId]) : 0;
        $array['category_id'] = (isset($data[Settings::paramCategoryId])) ? $data[Settings::paramCategoryId] : '';
        $array['row_created_on'] = date("Y-m-d H:i:s");
        $array['row_modified_on'] = date("Y-m-d H:i:s");
        $array['code'] = (isset($data[Tradefeed::nameProductCode])) ? $this->dbLink->escape($data[Tradefeed::nameProductCode], true) : '';
        $array['name'] = (isset($data[Tradefeed::nameProductName])) ? $this->dbLink->escape($data[Tradefeed::nameProductName], true) : '';
        $array['category'] = (isset($data[Tradefeed::nameProductCategory])) ? $this->dbLink->escape($data[Tradefeed::nameProductCategory], true) : '';
        $array['price'] = (isset($data[Tradefeed::nameProductPrice])) ? doubleval($data[Tradefeed::nameProductPrice]) : 0;
        $array['market_price'] = (isset($data[Tradefeed::nameProductMarketPrice])) ? doubleval($data[Tradefeed::nameProductMarketPrice]) : 0;
        $array['available_quantity'] = (isset($data[Tradefeed::nameProductAvailableQty])) ? intval($data[Tradefeed::nameProductAvailableQty]) : 0;
        $array['condition'] = (isset($data[Tradefeed::nameProductCondition])) ? $data[Tradefeed::nameProductCondition] : '';
        $array['image_url'] = (isset($data[Tradefeed::nameProductImageURL])) ? $data[Tradefeed::nameProductImageURL] : '';

        $images = '';
        if (isset($data[Tradefeed::nameProductImages])) {
            foreach ($data[Tradefeed::nameProductImages] as $image) {
                $images .= $this->tradefeed->section(Tradefeed::nameProductImageURL, $image, true, 4);
            }
        }
        $array['images'] = $images;
        $array['shipping_product_class'] = (isset($data[Tradefeed::nameProductShippingClass])) ? $this->dbLink->escape($data[Tradefeed::nameProductShippingClass], true) : '';
        $array['attr_custom_attrs'] = (isset($data[Tradefeed::nameProductAttributes])) ? $this->dbLink->escape($data[Tradefeed::nameProductAttributes], true) : '';

        return $array;
    }

    /*
     * Because PHP's integer type is signed and many platforms use 32bit integers,
     * some filesystem functions may return unexpected results for files which are larger than 2GB.
     */
    private function getFileSize($file) {
        $iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
        $exec_works = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');

        // try a shell command
        if ($exec_works) {
            $cmd = ($iswin) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
            @exec($cmd, $output);
            if (is_array($output) && ctype_digit($size = trim(implode("\n", $output)))) {
                return $size;
            }
        }

        // try the Windows COM interface
        if ($iswin && class_exists("\COM")) {
            try {
                $fsobj = new \COM('Scripting.FileSystemObject');
                $fileObject = $fsobj->GetFile(realpath($file));
                $size = $fileObject->Size;
            } catch (\Exception $e) {
                $size = null;
            }
            if (ctype_digit($size)) {
                return $size;
            }
        }

        // if all else fails
        return filesize($file);
    }

    /**
     * Get tradefeed object from CORE
     *
     * @return Tradefeed
     */
    public function getTradefeedInstance() {
        return $this->tradefeed;
    }

    /**
     * Get version object from CORE
     *
     * @return Version
     */
    public function getVersionInstance() {
        return $this->version;
    }

    /**
     * Call php function phpinfo()
     *
     * @return void print php info
     */
    public function phpInfo() {
        phpinfo();
    }

    /**
     * Get json encoded version metrics
     */
    private function jsonMetrics() {
        $metrics = $this->version->getMetrics();
        echo json_encode($metrics);
    }

    /**
     * Wrap exit instruction in class method
     *
     * @param mixed $status if string then display status message
     *
     * @return void
     */
    public function callExit($status = '') {
        exit($status);

    }

    /**
     * Add Zlib headers if extension is active
     * 
     * @param boolean $zlibEnabled zlib status(on/off)
     * 
     * @return mixed
     */
    protected function addZlibHeaders($zlibEnabled) {
        if ($zlibEnabled) {
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
        }
        return false;
    }
}
