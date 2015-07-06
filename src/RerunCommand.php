<?php
/**
 * Created by PhpStorm.
 * Date: 29.06.15
 * Time: 9:44
 *
 * Extends TextUI_Command to add the --rerun parameter
 *
 * @author Karl Klein <karl.klein@cashongo.co.uk>
 */

class Rerun_Command extends PHPUnit_TextUI_Command {

    public function __construct() {
        $this->longOptions['rerun'] = 'rerunHandler';
    }

    protected function rerunHandler(){
        $this->arguments['rerun'] = true;
    }

    protected function createRunner()
    {
        return new RerunTestRunner($this->arguments['loader']);
    }

    protected function showHelp() {
        parent::showHelp();
        print '  --rerun                   Runs tests that failed on last execution.';
    }
}