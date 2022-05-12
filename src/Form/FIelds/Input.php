<?php

namespace Symbiotic\Form\Fields;


class Input extends FieldAbstract
{
    /**
     * @var string
     */
    protected string $template = 'fields/input';

    public function __construct(array $data)
    {
        $this->data['attributes']['type'] = 'text';
        parent::__construct($data);
    }


}