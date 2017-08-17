<?php

namespace Hebis\Controller;

use VuFindAdmin\Controller\AbstractAdmin;

/**
 * Class shows the Logs
 *
 * @package Controller
 */
class AdminLogsController extends AbstractAdmin
{


    /**
     * Logs Details
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {

        $view = $this->createViewModel();

        $view->setTemplate('adminlogs/logs');
        $view->logContent = $this->getLogFileContent();
        $view->lineCount = $this->getLineCount();
        //$view->site = 'logs';

        return $view;
    }

    /**
     * @return String recent log
     */
    private function getLogFileContent()
    {
        $lineCount = $this->getLineCount();

        $path = $this->getRequest()->getServer("VUFIND_LOCAL_DIR");
        $path .= '/logs/hds.log';

        $log = $this->tail($path, $lineCount);

        return $log;

    }

    /**
     * @return mixed
     */
    private function getLineCount()
    {
        $config = $this->getConfig();
        $lineCount = $config["System"]["log_line_count"];
        return $lineCount;
    }

    /**
     * returns the last lines of a file (bottom-up)
     *
     * @return string recent log
     */
    private function tail($filepath, $lines = 200, $buffer = 4096)
    {
        $output = '';
        $f = fopen($filepath, "rb");

        fseek($f, -1, SEEK_END);

        if (fread($f, 1) != "\n") $lines -= 1;

        // Start reading from bottom backwards
        while (ftell($f) > 0 && $lines >= 0) {
            $seek = min(ftell($f), $buffer);

            fseek($f, -$seek, SEEK_CUR);

            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -strlen($chunk), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        fclose($f);
        return $output;
    }


}