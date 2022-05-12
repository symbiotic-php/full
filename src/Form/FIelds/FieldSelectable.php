<?php

namespace Symbiotic\Form\Fields;

use Symbiotic\Form\FieldSelectableInterface;

abstract class FieldSelectable extends FieldAbstract implements FieldSelectableInterface
{
    public function __construct(array $data = [])
    {
        $this->data['variants'] = [];
        parent::__construct($data);
    }

    /**
     * @param array $variants
     */
    public function variants(array $variants)
    {
        $this->data['variants'] = $variants;
    }

    /**
     * @return array
     */
    public function getVariants(): array
    {
        return $this->data['variants'];
    }
}