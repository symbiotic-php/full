<?php

namespace Symbiotic\Form;


abstract class Validator
{

    protected $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        return $this->data['message'];
    }

    abstract public function validate($value): bool;

}