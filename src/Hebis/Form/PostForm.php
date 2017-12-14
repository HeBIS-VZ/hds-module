<?php

namespace Hebis\Form;

use Zend\Form\Form;

/**
 * Class PostForm
 * @package Hebis\Form
 */
class PostForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'post',
            'type' => PostFieldset::class,
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Add new Post',
            ],
        ]);
    }
}