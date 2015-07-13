<?php
/**
 * Created by PhpStorm.
 * Date: 29.06.15
 * Time: 9:48
 *
 * No way to do this elegantly because of how doRun() in PHPUnit_TextUI_TestRunner works :(
 * Mostly copy paste.
 * Just added FailureListener and rerun handling
 *
 * @author Karl Klein <karl.klein@cashongo.co.uk>
 */

use SebastianBergmann\Environment\Runtime;
class RerunTestRunner extends PHPUnit_TextUI_TestRunner {

    /**
     * @var Runtime
     */
    private $runtime;

    /**
     * @var array
     */
    private $missingExtensions = array();

    public function __construct(PHPUnit_Runner_TestSuiteLoader $loader = null, PHP_CodeCoverage_Filter $filter = null) {
        parent::__construct($loader, $filter);
        $this->runtime = new Runtime;
    }


    public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array())
    {
        $this->handleConfiguration($arguments);

        $this->processSuiteFilters($suite, $arguments);

//      -----------------------
//      get the tests that failed last time this same command (without rerun) was used
//      from file and rerun them
        if (isset($arguments['rerun'])) {
            $cache = new CacheUtil;

            $key = $cache->generateKey($_SERVER['argv']);

            if ($cache->fileExists($key)) {
                $rerunnableTests = $cache->readCache($key);

                $suite = new PHPUnit_Framework_TestSuite();

                foreach ($rerunnableTests as $testName) {
                    $class = new ReflectionClass($testName['testClassName']);
                    $methodName = $testName['testMethodName'];
                    $test = $suite::createTest($class, $methodName);
                    $suite->addTest($test);
                }
            }
        }
//      ------------------------------


        if (isset($arguments['bootstrap'])) {
            $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $arguments['bootstrap'];
        }

        if ($arguments['backupGlobals'] === false) {
            $suite->setBackupGlobals(false);
        }

        if ($arguments['backupStaticAttributes'] === true) {
            $suite->setBackupStaticAttributes(true);
        }

        if ($arguments['disallowChangesToGlobalState'] === true) {
            $suite->setDisallowChangesToGlobalState(true);
        }

        if (is_integer($arguments['repeat'])) {
            $test = new PHPUnit_Extensions_RepeatedTest(
                $suite,
                $arguments['repeat'],
                $arguments['processIsolation']
            );

            $suite = new PHPUnit_Framework_TestSuite();
            $suite->addTest($test);
        }

        $result = $this->createTestResult();

        if (!$arguments['convertErrorsToExceptions']) {
            $result->convertErrorsToExceptions(false);
        }

        if (!$arguments['convertNoticesToExceptions']) {
            PHPUnit_Framework_Error_Notice::$enabled = false;
        }

        if (!$arguments['convertWarningsToExceptions']) {
            PHPUnit_Framework_Error_Warning::$enabled = false;
        }

        if ($arguments['stopOnError']) {
            $result->stopOnError(true);
        }

        if ($arguments['stopOnFailure']) {
            $result->stopOnFailure(true);
        }

        if ($arguments['stopOnIncomplete']) {
            $result->stopOnIncomplete(true);
        }

        if ($arguments['stopOnRisky']) {
            $result->stopOnRisky(true);
        }

        if ($arguments['stopOnSkipped']) {
            $result->stopOnSkipped(true);
        }

        if ($this->printer === null) {
            if (isset($arguments['printer']) &&
                $arguments['printer'] instanceof PHPUnit_Util_Printer) {
                $this->printer = $arguments['printer'];
            } else {
                $printerClass = 'PHPUnit_TextUI_ResultPrinter';

                if (isset($arguments['printer']) &&
                    is_string($arguments['printer']) &&
                    class_exists($arguments['printer'], false)) {
                    $class = new ReflectionClass($arguments['printer']);

                    if ($class->isSubclassOf('PHPUnit_TextUI_ResultPrinter')) {
                        $printerClass = $arguments['printer'];
                    }
                }

                $this->printer = new $printerClass(
                    isset($arguments['stderr']) ? 'php://stderr' : null,
                    $arguments['verbose'],
                    $arguments['colors'],
                    $arguments['debug'],
                    $arguments['columns']
                );
            }
        }

        if (!$this->printer instanceof PHPUnit_Util_Log_TAP) {
            $this->printer->write(
                PHPUnit_Runner_Version::getVersionString() . "\n"
            );

            self::$versionStringPrinted = true;

            if ($arguments['verbose']) {
                $this->printer->write(
                    sprintf(
                        "\nRuntime:\t%s",
                        $this->runtime->getNameWithVersion()
                    )
                );

                if ($this->runtime->hasXdebug()) {
                    $this->printer->write(
                        sprintf(
                            ' with Xdebug %s',
                            phpversion('xdebug')
                        )
                    );
                }

                if (isset($arguments['configuration'])) {
                    $this->printer->write(
                        sprintf(
                            "\nConfiguration:\t%s",
                            $arguments['configuration']->getFilename()
                        )
                    );
                }

                $this->printer->write("\n");
            }

            if (isset($arguments['deprecatedStrictModeOption'])) {
                print "Warning:\tDeprecated option \"--strict\" used\n";
            } elseif (isset($arguments['deprecatedStrictModeSetting'])) {
                print "Warning:\tDeprecated configuration setting \"strict\" used\n";
            }

            if (isset($arguments['deprecatedSeleniumConfiguration'])) {
                print "Warning:\tDeprecated configuration setting \"selenium\" used\n";
            }
        }

        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $result->addListener($this->printer);

        if (isset($arguments['testdoxHTMLFile'])) {
            $result->addListener(
                new PHPUnit_Util_TestDox_ResultPrinter_HTML(
                    $arguments['testdoxHTMLFile']
                )
            );
        }

        if (isset($arguments['testdoxTextFile'])) {
            $result->addListener(
                new PHPUnit_Util_TestDox_ResultPrinter_Text(
                    $arguments['testdoxTextFile']
                )
            );
        }

        $codeCoverageReports = 0;

        if (isset($arguments['coverageClover'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageCrap4J'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageHtml'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coveragePHP'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageText'])) {
            $codeCoverageReports++;
        }

        if (isset($arguments['coverageXml'])) {
            $codeCoverageReports++;
        }

        if (!$this->printer instanceof PHPUnit_Util_Log_TAP) {
            if ($codeCoverageReports > 0 && !$this->codeCoverageFilter->hasWhitelist()) {
                $this->printer->write("Warning:\tNo whitelist configured for code coverage\n");
            }

            $this->printer->write("\n");
        }

        if ($codeCoverageReports > 0 && (!extension_loaded('tokenizer') || !$this->runtime->canCollectCodeCoverage())) {
            if (!extension_loaded('tokenizer')) {
                $this->showExtensionNotLoadedMessage(
                    'tokenizer',
                    'No code coverage will be generated.'
                );
            } elseif (!extension_loaded('Xdebug')) {
                $this->showExtensionNotLoadedMessage(
                    'Xdebug',
                    'No code coverage will be generated.'
                );
            }

            $codeCoverageReports = 0;
        }

        if ($codeCoverageReports > 0) {
            $codeCoverage = new PHP_CodeCoverage(
                null,
                $this->codeCoverageFilter
            );

            $codeCoverage->setAddUncoveredFilesFromWhitelist(
                $arguments['addUncoveredFilesFromWhitelist']
            );

            $codeCoverage->setCheckForUnintentionallyCoveredCode(
                $arguments['strictCoverage']
            );

            $codeCoverage->setProcessUncoveredFilesFromWhitelist(
                $arguments['processUncoveredFilesFromWhitelist']
            );

            if (isset($arguments['forceCoversAnnotation'])) {
                $codeCoverage->setForceCoversAnnotation(
                    $arguments['forceCoversAnnotation']
                );
            }

            if (isset($arguments['mapTestClassNameToCoveredClassName'])) {
                $codeCoverage->setMapTestClassNameToCoveredClassName(
                    $arguments['mapTestClassNameToCoveredClassName']
                );
            }

            $result->setCodeCoverage($codeCoverage);
        }

        if ($codeCoverageReports > 1) {
            if (isset($arguments['cacheTokens'])) {
                $codeCoverage->setCacheTokens($arguments['cacheTokens']);
            }
        }

        if (isset($arguments['jsonLogfile'])) {
            $result->addListener(
                new PHPUnit_Util_Log_JSON($arguments['jsonLogfile'])
            );
        }

        if (isset($arguments['tapLogfile'])) {
            $result->addListener(
                new PHPUnit_Util_Log_TAP($arguments['tapLogfile'])
            );
        }

        if (isset($arguments['junitLogfile'])) {
            $result->addListener(
                new PHPUnit_Util_Log_JUnit(
                    $arguments['junitLogfile'],
                    $arguments['logIncompleteSkipped']
                )
            );
        }

        $result->beStrictAboutTestsThatDoNotTestAnything($arguments['reportUselessTests']);
        $result->beStrictAboutOutputDuringTests($arguments['disallowTestOutput']);
        $result->beStrictAboutTodoAnnotatedTests($arguments['disallowTodoAnnotatedTests']);
        $result->beStrictAboutTestSize($arguments['enforceTimeLimit']);
        $result->setTimeoutForSmallTests($arguments['timeoutForSmallTests']);
        $result->setTimeoutForMediumTests($arguments['timeoutForMediumTests']);
        $result->setTimeoutForLargeTests($arguments['timeoutForLargeTests']);

        if ($suite instanceof PHPUnit_Framework_TestSuite) {
            $suite->setRunTestInSeparateProcess($arguments['processIsolation']);
        }

//        --------------------------------------------

        $result->addListener(new PHPUnit_Framework_FailureListener());

//        -----------------------------------------------

        $suite->run($result);

        unset($suite);
        $result->flushListeners();

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $this->printer->printResult($result);
        }

        if (isset($codeCoverage)) {
            if (isset($arguments['coverageClover'])) {
                $this->printer->write(
                    "\nGenerating code coverage report in Clover XML format ..."
                );

                $writer = new PHP_CodeCoverage_Report_Clover;
                $writer->process($codeCoverage, $arguments['coverageClover']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coverageCrap4J'])) {
                $this->printer->write(
                    "\nGenerating Crap4J report XML file ..."
                );

                $writer = new PHP_CodeCoverage_Report_Crap4j;
                $writer->process($codeCoverage, $arguments['coverageCrap4J']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coverageHtml'])) {
                $this->printer->write(
                    "\nGenerating code coverage report in HTML format ..."
                );

                $writer = new PHP_CodeCoverage_Report_HTML(
                    $arguments['reportLowUpperBound'],
                    $arguments['reportHighLowerBound'],
                    sprintf(
                        ' and <a href="http://phpunit.de/">PHPUnit %s</a>',
                        PHPUnit_Runner_Version::id()
                    )
                );

                $writer->process($codeCoverage, $arguments['coverageHtml']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coveragePHP'])) {
                $this->printer->write(
                    "\nGenerating code coverage report in PHP format ..."
                );

                $writer = new PHP_CodeCoverage_Report_PHP;
                $writer->process($codeCoverage, $arguments['coveragePHP']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coverageText'])) {
                if ($arguments['coverageText'] == 'php://stdout') {
                    $outputStream = $this->printer;
                    $colors       = $arguments['colors'];
                } else {
                    $outputStream = new PHPUnit_Util_Printer($arguments['coverageText']);
                    $colors       = false;
                }

                $processor = new PHP_CodeCoverage_Report_Text(
                    $arguments['reportLowUpperBound'],
                    $arguments['reportHighLowerBound'],
                    $arguments['coverageTextShowUncoveredFiles'],
                    $arguments['coverageTextShowOnlySummary']
                );

                $outputStream->write(
                    $processor->process($codeCoverage, $colors)
                );
            }

            if (isset($arguments['coverageXml'])) {
                $this->printer->write(
                    "\nGenerating code coverage report in PHPUnit XML format ..."
                );

                $writer = new PHP_CodeCoverage_Report_XML;
                $writer->process($codeCoverage, $arguments['coverageXml']);

                $this->printer->write(" done\n");
                unset($writer);
            }
        }

        return $result;
    }

    private function processSuiteFilters(PHPUnit_Framework_TestSuite $suite, array $arguments)
    {
        if (!$arguments['filter'] &&
            empty($arguments['groups']) &&
            empty($arguments['excludeGroups'])) {
            return;
        }

        $filterFactory = new PHPUnit_Runner_Filter_Factory();

        if (!empty($arguments['excludeGroups'])) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Exclude'),
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['groups'])) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Include'),
                $arguments['groups']
            );
        }

        if ($arguments['filter']) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Test'),
                $arguments['filter']
            );
        }
        $suite->injectFilter($filterFactory);
    }

    private function showExtensionNotLoadedMessage($extension, $message = '')
    {
        if (isset($this->missingExtensions[$extension])) {
            return;
        }

        if (!empty($message)) {
            $message = ' ' . $message;
        }

        $this->showMessage(
            'The ' . $extension . ' extension is not loaded.' . $message . "\n"
        );

        $this->missingExtensions[$extension] = true;
    }

    private function showMessage($message, $exit = false)
    {
        $this->write($message . "\n");

        if ($exit) {
            exit(self::EXCEPTION_EXIT);
        }
    }
}