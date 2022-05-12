<?php

namespace Symbiotic\Form;


/**
 * Interface SelectableFieldInterface
 * @package Symbiotic\Form
 */
interface FieldSelectableInterface extends FillableInterface
{
    public function variants(array $variants);

    public function getVariants():array;
}