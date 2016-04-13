<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 14.02.16
 * Time: 15:04
 */

namespace Hebis\RecordDriver;


interface PicaRecordInterface
{
    public function getJournal();

    public function getSeries();

    public function getReviewed();

    public function getReview();

    /**
     * @deprecated
     * @return mixed
     */
    public function getCopies();

    public function getRetroUrl();
}