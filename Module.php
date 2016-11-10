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
                    return new View\Helper\Record\RecordGetSubFieldDataOfField();
                },
                'record_get_sub_fields_of_field_type' => function($sm) {
                    return new View\Helper\Record\RecordGetSubFieldsOfFieldType();
                },
                'result_list_title_statement' => function($sm) {
                    return new View\Helper\Record\ResultListTitleStatement();
                },
                'single_record_title_statement_headline' => function($sm) {
                    return new View\Helper\Record\SingleRecordTitleStatementHeadline();
                },
                'single_record_title_statement' => function($sm) {
                    return new View\Helper\Record\SingleRecordTitleStatement();
                },
                'single_record_title_statement_section_of_work' => function($sm) {
                    return new View\Helper\Record\SingleRecordTitleStatementSectionOfWork();
                },
                'result_list_main_entry_personal_name' => function($sm) {
                    return new View\Helper\Record\ResultListMainEntryPersonalName();
                },
                'single_record_main_entry_personal_name' => function($sm) {
                    return new View\Helper\Record\SingleRecordMainEntryPersonalName();
                },
                'result_list_added_entry_personal_name' => function($sm) {
                    return new View\Helper\Record\ResultListAddedEntryPersonalName();
                },
                'single_record_added_entry_personal_name' => function($sm) {
                    return new View\Helper\Record\SingleRecordAddedEntryPersonalName();
                },
                'single_record_dates_of_publication_sequential_designation' => function($sm) {
                    return new View\Helper\Record\SingleRecordDatesOfPublicationSequentialDesignation();
                },
                'single_record_festschrift' => function($sm) {
                    return new View\Helper\Record\SingleRecordFestschrift();
                },
                'single_record_interpreter' => function($sm) {
                    return new View\Helper\Record\SingleRecordInterpreter();
                },
                'single_record_marc_journal' => function($sm) {
                    return new View\Helper\Record\SingleRecordMarcJournal();
                },
                'single_record_publication_distribution' => function($sm) {
                    return new View\Helper\Record\SingleRecordPublicationDistribution();
                },
                'single_record_uniform_title' => function($sm) {
                    return new View\Helper\Record\SingleRecordUniformTitle();
                },
                'single_record_dissertation_note' => function($sm) {
                    return new View\Helper\Record\SingleRecordDissertationNote();
                },
                'single_record_other_edition_entry' => function($sm) {
                    return new View\Helper\Record\SingleRecordOtherEditionEntry();
                },
                'single_record_subject_access_fields_general_information' => function($sm) {
                    return new View\Helper\Record\SingleRecordSubjectAccessFieldsGeneralInformation();
                },
                'single_record_additional_physical_form_available_note' => function($sm) {
                    return new View\Helper\Record\SingleRecordAdditionalPhysicalFormAvailableNote();
                },
                'single_record_international_standard_book_number' => function($sm) {
                    return new View\Helper\Record\SingleRecordInternationalStandardBookNumber();
                },
                'single_record_international_standard_serial_number' => function($sm) {
                    return new View\Helper\Record\SingleRecordInternationalStandardSerialNumber();
                },
                'single_record_title' => function($sm) {
                    return new View\Helper\Record\SingleRecordTitle();

                },

                // Other View Helper
                'bibtip' => function($sm) {
                    return new View\Helper\Record\BibTip();
                }

            )
        );
    }
}
