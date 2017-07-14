<?php

namespace Hebis\Form;


use Zend\Form\Form;
use Zend\Form\Element;

class Add extends Form
{
    public function __construct()
    {
        parent::__construct('add');

        $title = new Element\Text('headline');
        $title->setLabel('Headline');
        $title->setAttribute('class', 'form-control');


        $content = new Element\Textarea('content');
        $content->setLabel('Content');
        $content->setAttribute('class', 'form-control');


        $submit = new Element\Submit('submit');
        $submit->setValue('Add Post');
        $submit->setAttribute('class', 'btn btn-primary');

        $this->add($title);
        $this->add($content);
        $this->add($submit);

    }
}