<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;

class StaticPost extends Gateway
{

    public function __construct($rowClass = 'VuFind\Db\Row\StaticPost'
    )
    {
        parent::__construct('static_post', $rowClass);
    }

    public function savePost(Post $post)
    {
        $data = array(
            'headline' => $post->headline,
            'content' => $post->content,
            'author' => $post->author,
            'date' => $post->dateAdded
        );

        $id = (int)$post->id;
        if ($id == 0) {
            $this->insert($data);
        } else {
            if ($this->getPost($id)) {
                $this->update($data, array('id' => $id));
            } else {
                throw new \Exception('Post id does not exist');
            }
        }
    }

    public function deletePost($id)
    {
        $this->delete(array('id' => (int)$id));
    }

    public function fetchAll()
    {
        return $this->select();
    }

}