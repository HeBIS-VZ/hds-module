<?php


namespace Hebis\Controller;

use Hebis\Db\Table\StaticPost;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use VuFindAdmin\Controller\AbstractAdmin;


/**
 * Class controls Static Pages administration
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class PageAdminController extends AbstractAdmin
{
    use TranslatorAwareTrait;
    use PageTrait;

    // define http status constants
    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';
    const STATUS_NEED_AUTH = 'NEED_AUTH';

    /**
     * @var StaticPost
     */
    protected $table;

    protected $outputMode;

    public function __construct(StaticPost $table, $translator)
    {
        $this->table = $table;
        $this->setTranslator($translator);
    }

    /**
     * Static Pages Administrator Home View
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function listAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('pageadmin/list');
        $rows = $this->table->getAll();
        $view->rows = $rows;
        return $view;
    }

    /** Returns a view for static page with admin template
     * @return \Zend\View\Model\ViewModel
     */
    public function previewAction()
    {
        $lang = $this->getTranslatorLocale();
        $pid = $this->params()->fromRoute('pid');
        return $this->prepareViewStaticPages($pid, $lang, 'pageadmin/view');
    }

    /**
     * @return mixed|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('pageadmin/edit');

        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $view->langs = $allLanguages;

        $pid = $this->params()->fromRoute('pid');
        $request = $this->getRequest();
        $rowSet = $this->table->getPostByPid($pid);
        $view->rowSet = $rowSet->getDataSource();;
        if (!$request->isPost()) {
            return $view;
        }

        $i = 0;
        foreach ($rowSet as $row) {
            $row->headline = $this->params()->fromPost('sp-headline')[$i];
            $row->content = $this->params()->fromPost('sp-content')[$i];
            $row->nav_title = $this->params()->fromPost('sp-nav-title')[$i];
            $row->save();
            ++$i;
        }

        return $this->redirect()->toRoute('pageadmin/preview', ['pid' => $pid]); //$this->forwardTo('staticpagesadmin', 'list');
    }

    /** Action adds new static page
     * @return mixed|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('pageadmin/add');
        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $view->langs = $allLanguages;
        // $sessionManager = $this->getServiceLocator()->get('VuFind\SessionManager');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $view;
        }

        $pid = $this->table->getLastPageID();
        $pid++;


        $language = $this->params()->fromPost('sp-lang');
        $headline = $this->params()->fromPost('sp-headline');
        $navtitle = $this->params()->fromPost('sp-nav-title');
        $content = $this->params()->fromPost('sp-content');
        $author = $this->params()->fromPost('sp-author');

        $notEmpty = false;

        for ($i = 0; $i < count($allLanguages); ++$i) {
            $contents = strip_tags($headline[$i] . $content[$i]);
            $len = strlen($contents);
            $notEmpty |= ($len > 0);
        }

        if (!$notEmpty) {
            $view->error = true;
            return $view;
        }

        for ($i = 0; $notEmpty && $i < sizeof($allLanguages); $i++) {
            $this->saveRow(
                $pid,
                $language[$i],
                $headline[$i],
                $navtitle[$i],
                $content[$i],
                $author
            );
        }
        return $this->redirect()->toRoute('pageadmin');
    }

    private function saveRow($pageid, $language, $headline, $navtitle, $content, $author)
    {
        $row = $this->table->createRow();
        $row->pid = $pageid;
        $row->language = $language;
        $row->headline = $headline;
        $row->nav_title = (strlen($navtitle) == 0) ? substr($headline, 0, 10) : $navtitle;
        $row->content = $content;
        $row->author = $author;
        $row->save();
    }


    /**
     * Send output data and exit.
     *
     * @param mixed $data The response data
     * @param string $status Status of the request
     * @param int $httpCode A custom HTTP Status Code
     *
     * @return \Zend\Http\Response
     * @throws \Exception
     */
    protected function output($data, $status, $httpCode = null)
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Cache-Control', 'no-cache, must-revalidate');
        $headers->addHeaderLine('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');

        if ($httpCode !== null) {

            $response->setStatusCode($httpCode);
        }
        if ($this->outputMode !== 'json') {
            throw new \Exception('Unsupported output mode: ' . $this->outputMode);
        } else {
            $headers->addHeaderLine('Content-type', 'application/javascript');
            $output = ['data' => $data, 'status' => $status];

            $response->setContent(json_encode($output));
            return $response;
        }
    }

    /**
     * change visibility of static page.
     *
     * @return \Zend\Http\Response
     */
    public function visibleAjax()
    {
        try {
            $pid = $this->params()->fromRoute('pid');
            $rows = $this->table->getPostByPid($pid);
            foreach ($rows as $row) {
                $row->visible == 1 ? $row->visible = 0 : $row->visible = 1;
                $row->save();
            }
        } catch (\Exception $e) {
            $this->output($e->getMessage() . '\n' . 'Change Visibility Failed!', self::STATUS_ERROR, 400);
        }

        $this->layout()->setTemplate('pageadmin/list');
        return $this->output($row->visible == 1, self::STATUS_OK, 200);
    }


    public function deleteAjax()
    {
        try {
            $pid = $this->params()->fromRoute('pid');
            $rows = $this->table->getPostByPid($pid);
            foreach ($rows as $row) {
                $row->delete();
            }
        } catch (\Exception $e) {
            return $this->output(0, self::STATUS_ERROR . '\n' . $e->getMessage(), 400);
        }
        return $this->output(1, self::STATUS_OK, 200);
    }

    public function jsonAction()
    {
        // Set the output mode to JSON:
        $this->outputMode = 'json';

        $callback = [$this, $this->params()->fromRoute('method') . 'Ajax'];
        if (is_callable($callback)) {
            try {
                return call_user_func($callback);
            } catch (\Exception $e) {
                $debugMsg = ('development' == APPLICATION_ENV)
                    ? ': ' . $e->getMessage() : '';
                return $this->output(
                    $this->translate('An error has occurred') . $debugMsg,
                    self::STATUS_ERROR,
                    500
                );
            }
        } else {
            return $this->output(
                $this->translate('Invalid Method'), self::STATUS_ERROR, 400
            );
        }
    }

}