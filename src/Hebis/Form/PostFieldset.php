<?php

namespace Hebis\Form;

use Zend\Form\Fieldset;

class PostFieldset extends Fieldset
{
    public function init()
    {
        $this->add([
            'type' => 'hidden',
            'name' => 'id',
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'headline',
            'options' => [
                'label' => 'Post Headline',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'content',
            'options' => [
                'label' => 'Post Content',
            ],
        ]);
    }
}

