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
                'physical_description_format' => function($sm) { //Format
                    return new View\Helper\Record\PhysicalDescriptionFormat();
                },
                'result_list_title_statement' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListTitleStatement();
                },
                'result_list_personal_name' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListPersonalName();
                },
                'result_list_corporate_name' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListCorporateName();
                },
                'result_list_edition_statement' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListEditionStatement();
                },
                'result_list_publication_distribution' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListPublication();
                },
                'result_list_host_item_entry' => function($sm) {
                    return new View\Helper\Record\ResultList\ResultListHostItemEntry();
                },
                'single_record_additional_physical_from_available_note' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordAdditionalPhysicalFromAvailableNote();
                },
                'single_record_reproduction_note' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordReproductionNote();
                },
                'single_record_cartographic_mathematical_data' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordCartographicMathematicalData();
                },
                'single_record_dates_of_publication_sequential_designation' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordDatesOfPublicationSequentialDesignation();
                },
                'single_record_physical_description' => function($sm) { //Umfang
                    return new View\Helper\Record\SingleRecord\SingleRecordPhysicalDescription();
                },
                'single_record_dissertation_note' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordDissertationNote();
                },
                'single_record_corporate_name' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordCorporateName();
                },
                'single_record_edition_statement' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordEditionStatement();
                },
                'single_record_host_item_entry' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordHostItemEntry();
                },
                'single_record_international_standard_book_number' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordInternationalStandardBookNumber();
                },
                'single_record_international_standard_music_number' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordInternationalStandardMusicNumber();
                },
                'single_record_international_standard_serial_number' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordInternationalStandardSerialNumber();
                },
                'single_record_language_code' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordLanguageCode();
                },
                'single_record_other_classification_number' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordOtherClassificationNumber();
                },
                'single_record_other_edition_entry' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordOtherEditionEntry();
                },
                'single_record_personal_name' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordPersonalName();
                },
                'single_record_preceding_succeeding_entry' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordPrecedingSucceedingEntry();
                },
                'single_record_manufacture' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordManufacture();
                },
                'single_record_production' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordProduction();
                },
                'single_record_publication' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordPublication();
                },
                'single_record_distribution' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordDistribution();
                },
                'single_record_section_of_a_work' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordSectionOfAWork();
                },
                'single_record_part_of_a_work' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordPartOfAWork();
                },
                'single_record_series_statement_added_entry' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordSeriesStatementAddedEntry();
                },
                'single_record_subject_access_fields_general_information' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordSubjectAccessFieldsGeneralInformation();
                },
                'single_record_target_audience_note' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordTargetAudienceNote();
                },
                'single_record_title_contains' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordTitleContains();
                },
                'single_record_title_statement' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordTitleStatement();
                },
                'single_record_title_statement_headline' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordTitleStatementHeadline();
                },
                'single_record_uniform_title' => function($sm) {
                    return new View\Helper\Record\SingleRecord\SingleRecordUniformTitle();
                },
                'other_edition_title_statement' => function($sm) {
                    return new View\Helper\Record\OtherEdition\OtherEditionTitleStatement();
                },
                'other_edition_edition_statement' => function($sm) {
                    return new View\Helper\Record\OtherEdition\OtherEditionEditionStatement();
                },
                'other_edition_publication' => function($sm) {
                    return new View\Helper\Record\OtherEdition\OtherEditionPublication();
                },
                'bib_tip_title_statement' => function($sm) {
                    return new View\Helper\Record\BibTip\BibTipTitleStatement();
                },
                'bib_tip_personal_name' => function($sm) {
                    return new View\Helper\Record\BibTip\BibTipPersonalName();
                },
                'bib_tip_publication' => function($sm) {
                    return new View\Helper\Record\BibTip\BibTipPublication();
                }
            )
        );
    }
}
