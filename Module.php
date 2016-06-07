<?php
/**
 * Template for ZF2 module for storing local overrides.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Module
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/dmj/vf2-proxy
 */
namespace Hebis;

use Zend\ModuleManager\ModuleManager,
    Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;


/**
 * Template for ZF2 module for storing local overrides.
 *
 * @category VuFind2
 * @package  Module
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/dmj/vf2-proxy
 */
class Module
{
    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * Initialize the module
     *
     * @param ModuleManager $m Module manager
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function init(ModuleManager $m)
    {
    }

    /**
     * Bootstrap the module
     *
     * @param MvcEvent $e Event
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onBootstrap(MvcEvent $e)
    {

    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'record_get_sub_field_data_of_field' => function($sm) {
                    return new View\Helper\RecordGetSubFieldDataOfField();
                },
                'record_get_sub_fields_of_field_type' => function($sm) {
                    return new View\Helper\RecordGetSubFieldsOfFieldType();
                },
                'single_record_title_statement' => function($sm) {
                    return new View\Helper\SingleRecordTitleStatement();
                },
                'single_record_title_statement_section_of_work' => function($sm) {
                    return new View\Helper\SingleRecordTitleStatementSectionOfWork();
                },
                'single_record_main_entry_personal_name' => function($sm) {
                    return new View\Helper\SingleRecordMainEntryPersonalName();
                },
                'single_record_added_entry_personal_name' => function($sm) {
                    return new View\Helper\SingleRecordAddedEntryPersonalName();
                },
                'single_record_festschrift' => function($sm) {
                    return new View\Helper\SingleRecordFestschrift();
                },
                'single_record_interpreter' => function($sm) {
                    return new View\Helper\SingleRecordInterpreter();
                },
                'single_record_marc_journal' => function($sm) {
                    return new View\Helper\SingleRecordMarcJournal();
                },
                'single_record_publication_distribution' => function($sm) {
                    return new View\Helper\SingleRecordPublicationDistribution();
                },
                'single_record_uniform_title' => function($sm) {
                    return new View\Helper\SingleRecordUniformTitle();
                },
                'single_record_dissertation_note' => function($sm) {
                    return new View\Helper\SingleRecordDissertationNote();
                },
                'single_record_other_edition_entry' => function($sm) {
                    return new View\Helper\SingleRecordOtherEditionEntry();
                },
                'single_record_subject_added_keywords' => function($sm) {
                    return new View\Helper\SingleRecordSubjectAddedKeywords();
                },
                'single_record_additional_physical_form_available_note' => function($sm) {
                    return new View\Helper\SingleRecordAdditionalPhysicalFormAvailableNote();
                },
                'single_record_international_standard_book_number' => function($sm) {
                    return new View\Helper\SingleRecordInternationalStandardBookNumber();
                },
                'single_record_international_standard_serial_number' => function($sm) {
                    return new View\Helper\SingleRecordInternationalStandardSerialNumber();
                }

            )
        );
    }
}
