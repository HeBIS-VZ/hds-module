<?php

namespace Hebis\View\Helper\Root;


use Hebis\Db\Table\Broadcast;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use Zend\View\Helper\AbstractHelper;

/**
 * class for static pages navigation
 *
 * @package Hebis\View\Helper\Root
 */
class BroadcastMessage extends AbstractHelper
{

    use TranslatorAwareTrait;

    /**
     * @var Broadcast
     */
    protected $table;

    public function __construct(Broadcast $table, $translator)
    {
        $this->table = $table;
        $this->setTranslator($translator);
    }


    public function __invoke()
    {
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getBroadcasts()
    {
        $arr = [];
        $lang = $this->getTranslatorLocale();
        $resultSet = $this->table->getAllByParameter($lang, 1, false);

        foreach ($resultSet as $broadcast) {
            $arr[] = [
                'bcid' => $broadcast->bcid,
                'message' => $broadcast->message,
                'type' => $broadcast->type
            ];
        }
        return $arr;
    }
}