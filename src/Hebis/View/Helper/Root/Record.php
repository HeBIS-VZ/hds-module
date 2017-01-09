<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 06.01.17
 * Time: 11:42
 */

namespace Hebis\View\Helper\Root;


class Record extends \VuFind\View\Helper\Root\Record
{

    public function __construct($config = null)
    {
        parent::__construct($config);
    }

    public function hasThumbnailCachedFile($size = 'small')
    {
        $thumb = parent::getThumbnail($size);

    }
}