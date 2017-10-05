<?php

namespace Hebis\Controller;

/**
 * Class RecordController
 * @package Hebis\Controller
 * @author ${USER} <${EMAIL}
 */
class RecordController extends \VuFind\Controller\RecordController
{

    public function showTab($tab, $ajax = false)
    {
        $view = parent::showTab($tab, $ajax);
        $config = $this->getConfig();
        $view->showPublicationDate = $config->Record->show_publication_date;
        return $view;
    }
}