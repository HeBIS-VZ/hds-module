<?php

namespace Hebis\Controller;


trait SaveSearchToHistoryTrait
{

    /**
     * Save a search to the history in the database.
     *
     * @param \VuFind\Search\Base\Results $results Search results
     *
     * @return void
     */
    protected function saveSearchToHistory($results)
    {
        $params = $this->params();
        $outputMode = $params->getController()->getOutputMode();
        // Remember the current URL as the last search.

        if ($outputMode !== "json") {
            $user = $this->getUser();
            $sessId = $this->getServiceLocator()->get('VuFind\SessionManager')->getId();
            $history = $this->getTable('Search');
            $history->saveSearch(
                $this->getResultsManager(), $results, $sessId,
                isset($user->id) ? $user->id : null
            );
        }
    }
}