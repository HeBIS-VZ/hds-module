<?php
/**
 * Search params plugin factory
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace Hebis\Search\Params;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Search params plugin factory
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class PluginFactory extends \VuFind\Search\Params\PluginFactory
{

    /**
     * Create a service for the specified name.
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     * @param string                  $name           Name of service
     * @param string                  $requestedName  Unfiltered name of service
     *
     * @return object
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator,
        $name, $requestedName
    ) {

        $options = $serviceLocator->getServiceLocator()
            ->get('VuFind\SearchOptionsPluginManager')->get($requestedName);

        // is name === solr?
        if (!strcasecmp("solr", $name)) {
            return new \Hebis\Search\Solr\Params(
            // Clone the options instance in case caller modifies it:
                clone($options),
                $serviceLocator->getServiceLocator()->get('VuFind\Config')
            );
        }
        if (!strcasecmp("EDS", $name)) {
            return new \Hebis\Search\EDS\Params(
            // Clone the options instance in case caller modifies it:
                clone($options),
                $serviceLocator->getServiceLocator()->get('VuFind\Config')
            );
        }

        if (!strcasecmp("solrauthor", $name)) {
            return new \Hebis\Search\SolrAuthor\Params(
            // Clone the options instance in case caller modifies it:
                clone($options),
                $serviceLocator->getServiceLocator()->get('VuFind\Config')
            );
        }

        if (!strcasecmp("favorites", $name)) {
            return new \Hebis\Search\Favorites\Params(
            // Clone the options instance in case caller modifies it:
                clone($options),
                $serviceLocator->getServiceLocator()->get('VuFind\Config')
            );
        }

        return parent::createServiceWithName($serviceLocator, $name, $requestedName);
    }
}
