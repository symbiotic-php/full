<?php

namespace Symbiotic\Form;


interface FillableInterface extends FieldInterface
{

    /**
     * @param string $name
     * @return FillableInterface
     */
    public function setName(string $name): FillableInterface;

    /**
     * @param string $label
     * @return FillableInterface
     */
    public function setLabel(string $label): FillableInterface;

    /**
     * @param string |array $value array for checkbox or multiselect
     * @return mixed
     */
    public function setValue($value): FillableInterface;


    /**
     * @param string|array $default array for checkbox or multiselect
     * @return mixed
     */
    public function setDefault($default): FillableInterface;

    /**
     * @param string $description
     * @return FillableInterface
     */
    public function setDescription(string $description): FillableInterface;


    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDotName(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string|array|null array for checkbox or multiselect, null if not filled
     */
    public function getValue();

    /**
     * @return string|array|null array for checkbox or multiselect, null if not filled
     */
    public function getDefault();

    /**
     * @return string
     */
    public function getDescription(): string;

    public function getAttributesHtml(): string;

    public function getValidators(): array;

    /**
     * @param string|array $value
     * @return bool
     */
    public function validate($value): bool;

    /**
     * @return string|null
     */
    public function getError(): ?string;


}

/**
 * o9Q3tUGHRKuV
 *  scp -r /home/api.interior.ru/* root@89.108.72.57:/home/api.interior.ru/
 */