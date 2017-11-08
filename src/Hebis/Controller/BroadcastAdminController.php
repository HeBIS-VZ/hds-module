<?php


namespace Hebis\Controller;

use Hebis\Db\Table\Broadcast;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use VuFindAdmin\Controller\AbstractAdmin;


/**
 * Class controls Static Pages administration
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class BroadcastAdminController extends AbstractAdmin
{
    use TranslatorAwareTrait;
    use PageTrait;

    /**
     * @var Broadcast
     */
    protected $table;

    public function __construct(Broadcast $table, $translator)
    {
        $this->table = $table;
        $this->setTranslator($translator);
    }

    public function addAction()
    {
        // TODO
    }

    public function listAction()
    {
        // TODO
    }


}