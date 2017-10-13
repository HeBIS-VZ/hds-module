<?php

namespace Hebis\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\Session\Container as SessionContainer;

class ResultScroller extends AbstractPlugin
{

    /**
     * Is scroller enabled?
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Session data used by scroller
     *
     * @var SessionContainer
     */
    protected $data;

    /**
     * Constructor. Create a new search result scroller.
     *
     * @param SessionContainer $session Session container
     * @param bool             $enabled Is the scroller enabled?
     */
    public function __construct(SessionContainer $session, $enabled = true)
    {
        $this->enabled = $enabled;

        // Set up session namespace for the class.
        $this->data = $session;
    }


    /**
     * Initialize this result set scroller. This should only be called
     * prior to displaying the results of a new search.
     *
     * @param $searchClassId
     * @param \VuFind\Search\Base\Results $searchObject The search object that was
     * used to execute the last search.
     *
     *
     * @return bool
     */
    public function init($searchClassId, $searchObject)
    {
        // Do nothing if disabled:
        if (!$this->enabled) {
            return false;
        }

        // Save the details of this search in the session
        $this->data->{$searchClassId.'_searchId'} = $searchObject->getSearchId();
        $this->data->{$searchClassId.'_page'} = $searchObject->getParams()->getPage();
        $this->data->{$searchClassId.'_limit'} = $searchObject->getParams()->getLimit();
        $this->data->{$searchClassId.'_sort'} = $searchObject->getParams()->getSort();
        $this->data->{$searchClassId.'_total'} = $searchObject->getResultTotal();
        $this->data->{$searchClassId.'_firstlast'} = $searchObject->getOptions()
            ->supportsFirstLastNavigation();

        // save the IDs of records on the current page to the session
        // so we can "slide" from one record to the next/previous records
        // spanning 2 consecutive pages
        $this->data->{$searchClassId.'_currIds'} = $this->fetchPage($searchObject);

        // clear the previous/next page
        unset($this->data->{$searchClassId.'_prevIds'});
        unset($this->data->{$searchClassId.'_nextIds'});
        unset($this->data->{$searchClassId.'_firstId'});
        unset($this->data->{$searchClassId.'_lastId'});

        return (bool)$this->data->{$searchClassId.'_currIds'};
    }

    /**
     * Return a modified results array to help scroll the user through the current
     * page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param int $pos Current position within current page
     * @return array
     */
    protected function scrollOnCurrentPage($searchClassId, $retVal, $pos)
    {
        $retVal['previousRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos - 1];
        $retVal['nextRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos + 1];
        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where the user is on the cusp of
     * the previous page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @param int $pos Current position within current
     * page
     * @param int $count Size of current page of results
     * @return array
     */
    protected function fetchPreviousPage($searchClassId, $retVal, $lastSearch, $pos, $count)
    {
        // if the current page is NOT the first page, and
        // the previous page has not been fetched before, then
        // fetch the previous page
        if ($this->data->{$searchClassId.'_page'} > 1 && $this->data->{$searchClassId.'_prevIds'} == null) {
            $this->data->{$searchClassId.'_prevIds'} = $this->fetchPage(
                $lastSearch, $this->data->{$searchClassId . '_page'} - 1
            );
        }

        // if there is something on the previous page, then the previous
        // record is the last record on the previous page
        if (!empty($this->data->{$searchClassId.'_prevIds'})) {
            $retVal['previousRecord']
                = $this->data->{$searchClassId.'_prevIds'}[count($this->data->{$searchClassId.'_prevIds'}) - 1];
        }

        // if it is not the last record on the current page, then
        // we also have a next record on the current page
        if ($pos < $count - 1) {
            $retVal['nextRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos + 1];
        }

        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where the user is on the cusp of
     * the next page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @param int $pos Current position within current
     * page
     * @return array
     */
    protected function fetchNextPage($searchClassId, $retVal, $lastSearch, $pos)
    {
        // if the current page is NOT the last page, and the next page has not been
        // fetched, then fetch the next page
        if ($this->data->{$searchClassId.'_page'} < ceil($this->data->{$searchClassId.'_total'} / $this->data->{$searchClassId.'_limit'})
            && $this->data->{$searchClassId.'_nextIds'} == null
        ) {
            $this->data->{$searchClassId.'_nextIds'} = $this->fetchPage(
                $lastSearch, $this->data->{$searchClassId.'_page'} + 1
            );
        }

        // if there is something on the next page, then the next
        // record is the first record on the next page
        if (is_array($this->data->{$searchClassId.'_nextIds'}) && count($this->data->{$searchClassId.'_nextIds'}) > 0) {
            $retVal['nextRecord'] = $this->data->{$searchClassId.'_nextIds'}[0];
        }

        // if it is not the first record on the current page, then
        // we also have a previous record on the current page
        if ($pos > 0) {
            $retVal['previousRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos - 1];
        }

        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where we need to retrieve data
     * from the previous page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @param int $pos Current position within
     * previous page
     * @return array
     */
    protected function scrollToPreviousPage($searchClassId, $retVal, $lastSearch, $pos)
    {
        // decrease the page in the session because
        // we're now sliding into the previous page
        // (-- doesn't work on ArrayObjects)
        $this->data->{$searchClassId.'_page'} = $this->data->{$searchClassId.'_page'} - 1;

        // shift pages to the right
        $tmp = $this->data->{$searchClassId.'_currIds'};
        $this->data->{$searchClassId.'_currIds'} = $this->data->{$searchClassId.'_prevIds'};
        $this->data->{$searchClassId.'_nextIds'} = $tmp;
        $this->data->{$searchClassId.'_prevIds'} = null;

        // now we can set the previous/next record
        if ($pos > 0) {
            $retVal['previousRecord']
                = $this->data->{$searchClassId.'_currIds'}[$pos - 1];
        }
        $retVal['nextRecord'] = $this->data->{$searchClassId.'_nextIds'}[0];

        // recalculate the current position
        $retVal['currentPosition']
            = ($this->data->{$searchClassId.'_page'} - 1)
            * $this->data->{$searchClassId.'_limit'} + $pos + 1;

        // update the search URL in the session
        $lastSearch->getParams()->setPage($this->data->{$searchClassId.'_page'});
        $this->rememberSearch($lastSearch);

        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where we need to retrieve data
     * from the next page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @param int $pos Current position within next
     * page
     * @return array
     */
    protected function scrollToNextPage($searchClassId, $retVal, $lastSearch, $pos)
    {
        // increase the page in the session because
        // we're now sliding into the next page
        // (++ doesn't work on ArrayObjects)
        $this->data->{$searchClassId.'_page'} = $this->data->{$searchClassId.'_page'} + 1;

        // shift pages to the left
        $tmp = $this->data->{$searchClassId.'_currIds'};
        $this->data->{$searchClassId.'_currIds'} = $this->data->{$searchClassId.'_nextIds'};
        $this->data->{$searchClassId.'_prevIds'} = $tmp;
        $this->data->{$searchClassId.'_nextIds'} = null;

        // now we can set the previous/next record
        $retVal['previousRecord']
            = $this->data->{$searchClassId.'_prevIds'}[count($this->data->{$searchClassId.'_prevIds'}) - 1];
        if ($pos < count($this->data->{$searchClassId.'_currIds'}) - 1) {
            $retVal['nextRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos + 1];
        }

        // recalculate the current position
        $retVal['currentPosition']
            = ($this->data->{$searchClassId.'_page'} - 1)
            * $this->data->{$searchClassId.'_limit'} + $pos + 1;

        // update the search URL in the session
        $lastSearch->getParams()->setPage($this->data->{$searchClassId.'_page'});
        $this->rememberSearch($lastSearch);

        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where we need to retrieve data
     * from the the first page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @return array
     */
    protected function scrollToFirstRecord($searchClassId, $retVal, $lastSearch)
    {
        // Set page in session to First Page
        $this->data->{$searchClassId.'_page'} = 1;
        // update the search URL in the session
        $lastSearch->getParams()->setPage($this->data->{$searchClassId.'_page'});
        $this->rememberSearch($lastSearch);

        // update current, next and prev Ids
        $this->data->{$searchClassId.'_currIds'} = $this->fetchPage($lastSearch, $this->data->{$searchClassId.'_page'});
        $this->data->{$searchClassId.'_nextIds'} = $this->fetchPage($lastSearch, $this->data->{$searchClassId.'_page'} + 1);
        $this->data->{$searchClassId.'_prevIds'} = null;

        // now we can set the previous/next record
        $retVal['previousRecord'] = null;
        $retVal['nextRecord'] = isset($this->data->{$searchClassId.'_currIds'}[1])
            ? $this->data->{$searchClassId.'_currIds'}[1] : null;
        // cover extremely unlikely edge case -- page size of 1:
        if (null === $retVal['nextRecord'] && isset($this->data->{$searchClassId.'_nextIds'}[0])) {
            $retVal['nextRecord'] = $this->data->{$searchClassId.'_nextIds'}[0];
        }

        // recalculate the current position
        $retVal['currentPosition'] = 1;

        // and we're done
        return $retVal;
    }

    /**
     * Return a modified results array for the case where we need to retrieve data
     * from the the last page of results
     *
     * @param $searchClassId
     * @param array $retVal Return values (in progress)
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @return array
     */
    protected function scrollToLastRecord($searchClassId, $retVal, $lastSearch)
    {
        // Set page in session to Last Page
        $this->data->{$searchClassId.'_page'} = $this->getLastPageNumber($searchClassId);
        // update the search URL in the session
        $lastSearch->getParams()->setPage($this->data->{$searchClassId.'_page'});
        $this->rememberSearch($lastSearch);

        // update current, next and prev Ids
        $this->data->{$searchClassId.'_currIds'} = $this->fetchPage($lastSearch, $this->data->{$searchClassId.'_page'});
        $this->data->{$searchClassId.'_prevIds'} = $this->fetchPage($lastSearch, $this->data->{$searchClassId.'_page'} - 1);
        $this->data->{$searchClassId.'_nextIds'} = null;

        // recalculate the current position
        $retVal['currentPosition'] = $this->data->{$searchClassId.'_total'};

        // now we can set the previous/next record
        $retVal['nextRecord'] = null;
        if (count($this->data->{$searchClassId.'_currIds'}) > 1) {
            $pos = count($this->data->{$searchClassId.'_currIds'}) - 2;
            $retVal['previousRecord'] = $this->data->{$searchClassId.'_currIds'}[$pos];
        } else if (count($this->data->{$searchClassId.'_prevIds'}) > 0) {
            $prevPos = count($this->data->{$searchClassId.'_prevIds'}) - 1;
            $retVal['previousRecord'] = $this->data->{$searchClassId.'_prevIds'}[$prevPos];
        }

        // and we're done
        return $retVal;
    }

    /**
     * Get the ID of the first record in the result set.
     *
     * @param $searchClassId
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @return string
     */
    protected function getFirstRecordId($searchClassId, $lastSearch)
    {
        if (!isset($this->data->{$searchClassId.'_firstId'})) {
            $firstPage = $this->fetchPage($lastSearch, 1);
            $this->data->{$searchClassId.'_firstId'} = $firstPage[0];
        }
        return $this->data->{$searchClassId.'_firstId'};
    }

    /**
     * Calculate the last page number in the result set.
     *
     * @param $searchClassId
     * @return int
     */
    protected function getLastPageNumber($searchClassId)
    {
        return ceil($this->data->{$searchClassId.'_total'} / $this->data->{$searchClassId.'_limit'});
    }

    /**
     * Get the ID of the last record in the result set.
     *
     * @param $searchClassId
     * @param \VuFind\Search\Base\Results $lastSearch Representation of last search
     * @return string
     */
    protected function getLastRecordId($searchClassId, $lastSearch)
    {
        if (!isset($this->data->{$searchClassId.'_lastId'})) {
            $results = $this->fetchPage($lastSearch, $this->getLastPageNumber($searchClassId));
            $this->data->{$searchClassId.'_lastId'} = array_pop($results);
        }
        return $this->data->{$searchClassId.'_lastId'};
    }

    /**
     * Get the previous/next record in the last search
     * result set relative to the current one, also return
     * the position of the current record in the result set.
     * Return array('previousRecord'=>previd, 'nextRecord'=>nextid,
     * 'currentPosition'=>number, 'resultTotal'=>number).
     *
     * @param $searchClassId
     * @param \VuFind\RecordDriver\AbstractBase $driver Driver for the record
     * currently being displayed
     * @return array
     */
    public function getScrollData($searchClassId, $driver)
    {
        $retVal = [
            'firstRecord' => null, 'lastRecord' => null,
            'previousRecord' => null, 'nextRecord' => null,
            'currentPosition' => null, 'resultTotal' => null
        ];

        // Do nothing if disabled or data missing:
        if ($this->enabled
            && isset($this->data->{$searchClassId.'_currIds'}) && isset($this->data->{$searchClassId.'_searchId'})
            && ($lastSearch = $this->restoreLastSearch($searchClassId))
        ) {
            // Make sure expected data elements are populated:
            if (!isset($this->data->{$searchClassId.'_prevIds'})) {
                $this->data->{$searchClassId.'_prevIds'} = null;
            }
            if (!isset($this->data->{$searchClassId.'_nextIds'})) {
                $this->data->{$searchClassId.'_nextIds'} = null;
            }

            // Store total result set size:
            $retVal['resultTotal']
                = isset($this->data->{$searchClassId.'_total'}) ? $this->data->{$searchClassId.'_total'} : 0;

            // Set first and last record IDs
            if ($this->data->{$searchClassId.'_firstlast'}) {
                $retVal['firstRecord'] = $this->getFirstRecordId($searchClassId, $lastSearch);
                $retVal['lastRecord'] = $this->getLastRecordId($searchClassId, $lastSearch);
            }

            // build a full ID string using the driver:
            $id = $driver->getSourceIdentifier() . '|' . $driver->getUniqueId();

            // find where this record is in the current result page
            $pos = is_array($this->data->{$searchClassId.'_currIds'})
                ? array_search($id, $this->data->{$searchClassId.'_currIds'})
                : false;
            if ($pos !== false) {
                // OK, found this record in the current result page
                // calculate its position relative to the result set
                $retVal['currentPosition']
                    = ($this->data->{$searchClassId.'_page'} - 1) * $this->data->{$searchClassId.'_limit'} + $pos + 1;

                // count how many records in the current result page
                $count = count($this->data->{$searchClassId.'_currIds'});
                if ($pos > 0 && $pos < $count - 1) {
                    // the current record is somewhere in the middle of the current
                    // page, ie: not first or last
                    return $this->scrollOnCurrentPage($searchClassId, $retVal, $pos);
                } else if ($pos == 0) {
                    // this record is first record on the current page
                    return $this
                        ->fetchPreviousPage($searchClassId, $retVal, $lastSearch, $pos, $count);
                } else if ($pos == $count - 1) {
                    // this record is last record on the current page
                    return $this->fetchNextPage($searchClassId, $retVal, $lastSearch, $pos);
                }
            } else {
                // the current record is not on the current page
                // if there is something on the previous page
                if (!empty($this->data->{$searchClassId.'_prevIds'})) {
                    // check if current record is on the previous page
                    $pos = is_array($this->data->{$searchClassId.'_prevIds'})
                        ? array_search($id, $this->data->{$searchClassId.'_prevIds'}) : false;
                    if ($pos !== false) {
                        return $this
                            ->scrollToPreviousPage($searchClassId, $retVal, $lastSearch, $pos);
                    }
                }
                // if there is something on the next page
                if (!empty($this->data->{$searchClassId.'_nextIds'})) {
                    // check if current record is on the next page
                    $pos = is_array($this->data->{$searchClassId.'_nextIds'})
                        ? array_search($id, $this->data->{$searchClassId.'_nextIds'}) : false;
                    if ($pos !== false) {
                        return $this->scrollToNextPage($searchClassId, $retVal, $lastSearch, $pos);
                    }
                }
                if ($this->data->{$searchClassId.'_firstlast'}) {
                    if ($id == $retVal['firstRecord']) {
                        return $this->scrollToFirstRecord($searchClassId, $retVal, $lastSearch);
                    }
                    if ($id == $retVal['lastRecord']) {
                        return $this->scrollToLastRecord($searchClassId, $retVal, $lastSearch);
                    }
                }
            }
        }
        return $retVal;
    }

    /**
     * Fetch the given page of results from the given search object and
     * return the IDs of the records in an array.
     *
     * @param $searchClassId
     * @param object $searchObject The search object to use to execute the search
     * @param int $page The page number to fetch (null for current)
     * @return array
     */
    protected function fetchPage($searchObject, $page = null)
    {
        if (null !== $page) {
            $searchObject->getParams()->setPage($page);
            $searchObject->performAndProcessSearch();
        }

        $retVal = [];
        foreach ($searchObject->getResults() as $record) {
            if (!($record instanceof \VuFind\RecordDriver\AbstractBase)) {
                return false;
            }
            $retVal[]
                = $record->getSourceIdentifier() . '|' . $record->getUniqueId();
        }
        return $retVal;
    }

    /**
     * Restore the last saved search.
     *
     * @return \VuFind\Search\Base\Results
     */
    protected function restoreLastSearch($searchClassId)
    {
        if (isset($this->data->{$searchClassId.'_searchId'})) {
            $searchTable = $this->getController()->getTable('Search');
            $row = $searchTable->getRowById($this->data->{$searchClassId.'_searchId'}, false);
            if (!empty($row)) {
                $minSO = $row->getSearchObject();
                $manager = $this->getController()->getServiceLocator()
                    ->get('VuFind\SearchResultsPluginManager');
                $search = $minSO->deminify($manager);
                // The saved search does not remember its original limit;
                // we should reapply it from the session data:
                $search->getParams()->setLimit($this->data->{$searchClassId.'_limit'});
                $search->getParams()->setSort($this->data->{$searchClassId.'_sort'});
                return $search;
            }
        }
        return null;
    }

    /**
     * Update the remembered "last search" in the session.
     *
     * @param \VuFind\Search\Base\Results $search Search object to remember.
     *
     * @return void
     */
    protected function rememberSearch($search)
    {
        $baseUrl = $this->getController()->url()->fromRoute(
            $search->getOptions()->getSearchAction()
        );
        $this->getController()->getSearchMemory()->rememberSearch(
            $baseUrl . $search->getUrlQuery()->getParams(false)
        );
    }
}
