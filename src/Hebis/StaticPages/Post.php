<?php
/**
 * Created by PhpStorm.
 * User: rosh
 * Date: 24.07.17
 * Time: 15:42
 */

namespace Hebis\StaticPages;


class Post
{
    public $id;
    public $headline;
    public $content;
    public $author;
    public $dateAdded;


    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->headline = (!empty($data['artist'])) ? $data['artist'] : null;
        $this->content = (!empty($data['title'])) ? $data['title'] : null;
        $this->author = (!empty($data['author'])) ? $data['author'] : null;
        $this->dateAdded = (!empty($data['$dateAdded'])) ? $data['$dateAdded'] : null;
    }

}