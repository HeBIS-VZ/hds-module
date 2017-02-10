<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 14:15
 */

namespace Hebis\Csl\Model;


use Hebis\Csl\Model\Layout\CslRecord;
use Zend\Stdlib\JsonSerializable;

class Record implements CslRecord, JsonSerializable
{

    use JsonSerializeTrait;

    private $author;

    private $collectionEditor;

    private $composer;

    private $containerAuthor;

    private $director;

    private $editor;

    private $editorialDirector;

    private $illustrator;

    private $interviewer;

    private $recipient;

    private $reviewedAuthor;

    private $translator;

    private $accessed;

    private $eventDate;

    private $issued;

    private $originalDate;

    private $submitted;


    private $chapterNumber;

    private $collectionNumber;

    private $edition;

    private $issue;

    /**
     * number identifying the item (e.g. a report number)
     * @var string
     */
    private $number;

    /**
     * total number of pages of the cited item
     * @var string
     */
    private $numberOfPages;

    /**
     * total number of volumes, usable for citing multi-volume books and such volume
     * (container) volume holding the item (e.g. “2” when citing a chapter from book volume 2)
     * @var
     */
    private $numberOfVolumes;

    private $volume;

    private $abstract;

    private $annote;

    private $archive;

    private $archive_location;

    private $archive_place;

    private $authority;

    private $callNumber;

    private $citationLabel;

    private $citationNumber;

    private $collectionTitle;

    private $containerTitle;

    private $containerTitleShort;

    private $dimensions;

    private $DOI;

    private $event;

    private $eventPlace;

    private $genre;

    private $ISBN;

    private $ISSN;

    private $jurisdiction;

    private $keyword;

    private $locator;

    private $medium;

    private $note;

    private $originalPublisher;

    private $originalPublisherPlace;

    private $originalTitle;

    private $page;

    private $pageFirst;

    private $publisher;

    private $publisherPlace;

    private $references;

    private $reviewedTitle;

    private $scale;

    private $section;

    private $source;

    private $status;

    private $title;

    private $titleShort;

    private $URL;

    private $version;

    private $yearSuffix;

    private $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     * @return Record
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollectionEditor()
    {
        return $this->collectionEditor;
    }

    /**
     * @param mixed $collectionEditor
     * @return Record
     */
    public function setCollectionEditor($collectionEditor)
    {
        $this->collectionEditor = $collectionEditor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * @param mixed $composer
     * @return Record
     */
    public function setComposer($composer)
    {
        $this->composer = $composer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainerAuthor()
    {
        return $this->containerAuthor;
    }

    /**
     * @param mixed $containerAuthor
     * @return Record
     */
    public function setContainerAuthor($containerAuthor)
    {
        $this->containerAuthor = $containerAuthor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * @param mixed $director
     * @return Record
     */
    public function setDirector($director)
    {
        $this->director = $director;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * @param mixed $editor
     * @return Record
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEditorialDirector()
    {
        return $this->editorialDirector;
    }

    /**
     * @param mixed $editorialDirector
     * @return Record
     */
    public function setEditorialDirector($editorialDirector)
    {
        $this->editorialDirector = $editorialDirector;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * @param mixed $illustrator
     * @return Record
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterviewer()
    {
        return $this->interviewer;
    }

    /**
     * @param mixed $interviewer
     * @return Record
     */
    public function setInterviewer($interviewer)
    {
        $this->interviewer = $interviewer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     * @return Record
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReviewedAuthor()
    {
        return $this->reviewedAuthor;
    }

    /**
     * @param mixed $reviewedAuthor
     * @return Record
     */
    public function setReviewedAuthor($reviewedAuthor)
    {
        $this->reviewedAuthor = $reviewedAuthor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param mixed $translator
     * @return Record
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessed()
    {
        return $this->accessed;
    }

    /**
     * @param mixed $accessed
     * @return Record
     */
    public function setAccessed($accessed)
    {
        $this->accessed = $accessed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @param mixed $eventDate
     * @return Record
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * @param mixed $issued
     * @return Record
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalDate()
    {
        return $this->originalDate;
    }

    /**
     * @param mixed $originalDate
     * @return Record
     */
    public function setOriginalDate($originalDate)
    {
        $this->originalDate = $originalDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param mixed $submitted
     * @return Record
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChapterNumber()
    {
        return $this->chapterNumber;
    }

    /**
     * @param mixed $chapterNumber
     * @return Record
     */
    public function setChapterNumber($chapterNumber)
    {
        $this->chapterNumber = $chapterNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollectionNumber()
    {
        return $this->collectionNumber;
    }

    /**
     * @param mixed $collectionNumber
     * @return Record
     */
    public function setCollectionNumber($collectionNumber)
    {
        $this->collectionNumber = $collectionNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * @param mixed $edition
     * @return Record
     */
    public function setEdition($edition)
    {
        $this->edition = $edition;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param mixed $issue
     * @return Record
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Record
     */
    public function setNumber(string $number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * @param string $numberOfPages
     * @return Record
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberOfVolumes()
    {
        return $this->numberOfVolumes;
    }

    /**
     * @param mixed $numberOfVolumes
     * @return Record
     */
    public function setNumberOfVolumes($numberOfVolumes)
    {
        $this->numberOfVolumes = $numberOfVolumes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @param mixed $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * @return mixed
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param mixed $abstract
     * @return Record
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnnote()
    {
        return $this->annote;
    }

    /**
     * @param mixed $annote
     * @return Record
     */
    public function setAnnote($annote)
    {
        $this->annote = $annote;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @param mixed $archive
     * @return Record
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArchive_location()
    {
        return $this->archive_location;
    }

    /**
     * @param mixed $archive_location
     * @return Record
     */
    public function setArchive_location($archive_location)
    {
        $this->archive_location = $archive_location;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArchive_place()
    {
        return $this->archive_place;
    }

    /**
     * @param mixed $archive_place
     * @return Record
     */
    public function setArchive_place($archive_place)
    {
        $this->archive_place = $archive_place;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @param mixed $authority
     * @return Record
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallNumber()
    {
        return $this->callNumber;
    }

    /**
     * @param mixed $callNumber
     * @return Record
     */
    public function setCallNumber($callNumber)
    {
        $this->callNumber = $callNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCitationLabel()
    {
        return $this->citationLabel;
    }

    /**
     * @param mixed $citationLabel
     * @return Record
     */
    public function setCitationLabel($citationLabel)
    {
        $this->citationLabel = $citationLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCitationNumber()
    {
        return $this->citationNumber;
    }

    /**
     * @param mixed $citationNumber
     * @return Record
     */
    public function setCitationNumber($citationNumber)
    {
        $this->citationNumber = $citationNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollectionTitle()
    {
        return $this->collectionTitle;
    }

    /**
     * @param mixed $collectionTitle
     * @return Record
     */
    public function setCollectionTitle($collectionTitle)
    {
        $this->collectionTitle = $collectionTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainerTitle()
    {
        return $this->containerTitle;
    }

    /**
     * @param mixed $containerTitle
     * @return Record
     */
    public function setContainerTitle($containerTitle)
    {
        $this->containerTitle = $containerTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainerTitleShort()
    {
        return $this->containerTitleShort;
    }

    /**
     * @param mixed $containerTitleShort
     * @return Record
     */
    public function setContainerTitleShort($containerTitleShort)
    {
        $this->containerTitleShort = $containerTitleShort;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param mixed $dimensions
     * @return Record
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDOI()
    {
        return $this->DOI;
    }

    /**
     * @param mixed $DOI
     * @return Record
     */
    public function setDOI($DOI)
    {
        $this->DOI = $DOI;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     * @return Record
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventPlace()
    {
        return $this->eventPlace;
    }

    /**
     * @param mixed $eventPlace
     * @return Record
     */
    public function setEventPlace($eventPlace)
    {
        $this->eventPlace = $eventPlace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param mixed $genre
     * @return Record
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getISBN()
    {
        return $this->ISBN;
    }

    /**
     * @param mixed $ISBN
     * @return Record
     */
    public function setISBN($ISBN)
    {
        $this->ISBN = $ISBN;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getISSN()
    {
        return $this->ISSN;
    }

    /**
     * @param mixed $ISSN
     * @return Record
     */
    public function setISSN($ISSN)
    {
        $this->ISSN = $ISSN;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJurisdiction()
    {
        return $this->jurisdiction;
    }

    /**
     * @param mixed $jurisdiction
     * @return Record
     */
    public function setJurisdiction($jurisdiction)
    {
        $this->jurisdiction = $jurisdiction;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $keyword
     * @return Record
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @param mixed $locator
     * @return Record
     */
    public function setLocator($locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMedium()
    {
        return $this->medium;
    }

    /**
     * @param mixed $medium
     * @return Record
     */
    public function setMedium($medium)
    {
        $this->medium = $medium;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     * @return Record
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPublisher()
    {
        return $this->originalPublisher;
    }

    /**
     * @param mixed $originalPublisher
     * @return Record
     */
    public function setOriginalPublisher($originalPublisher)
    {
        $this->originalPublisher = $originalPublisher;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPublisherPlace()
    {
        return $this->originalPublisherPlace;
    }

    /**
     * @param mixed $originalPublisherPlace
     * @return Record
     */
    public function setOriginalPublisherPlace($originalPublisherPlace)
    {
        $this->originalPublisherPlace = $originalPublisherPlace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalTitle()
    {
        return $this->originalTitle;
    }

    /**
     * @param mixed $originalTitle
     * @return Record
     */
    public function setOriginalTitle($originalTitle)
    {
        $this->originalTitle = $originalTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     * @return Record
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageFirst()
    {
        return $this->pageFirst;
    }

    /**
     * @param mixed $pageFirst
     * @return Record
     */
    public function setPageFirst($pageFirst)
    {
        $this->pageFirst = $pageFirst;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param mixed $publisher
     * @return Record
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublisherPlace()
    {
        return $this->publisherPlace;
    }

    /**
     * @param mixed $publisherPlace
     * @return Record
     */
    public function setPublisherPlace($publisherPlace)
    {
        $this->publisherPlace = $publisherPlace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param mixed $references
     * @return Record
     */
    public function setReferences($references)
    {
        $this->references = $references;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReviewedTitle()
    {
        return $this->reviewedTitle;
    }

    /**
     * @param mixed $reviewedTitle
     * @return Record
     */
    public function setReviewedTitle($reviewedTitle)
    {
        $this->reviewedTitle = $reviewedTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param mixed $scale
     * @return Record
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     * @return Record
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     * @return Record
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Record
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Record
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitleShort()
    {
        return $this->titleShort;
    }

    /**
     * @param mixed $titleShort
     * @return Record
     */
    public function setTitleShort($titleShort)
    {
        $this->titleShort = $titleShort;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getURL()
    {
        return $this->URL;
    }

    /**
     * @param mixed $URL
     * @return Record
     */
    public function setURL($URL)
    {
        $this->URL = $URL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     * @return Record
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYearSuffix()
    {
        return $this->yearSuffix;
    }

    /**
     * @param mixed $yearSuffix
     * @return Record
     */
    public function setYearSuffix($yearSuffix)
    {
        $this->yearSuffix = $yearSuffix;
        return $this;
    }

}