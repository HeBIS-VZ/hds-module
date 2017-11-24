<?php


namespace Hebis\Controller;

use Hebis\Db\Table\Broadcast;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use VuFind\Controller\AbstractBase;


/**
 * Class controls Broadcasts
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class BroadcastController extends AbstractBase
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

}