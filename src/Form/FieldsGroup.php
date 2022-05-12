<?php

namespace Symbiotic\Form;


use function _S\collect;
use function _S\view;
use function Symbiotic\Core\View\render;

/**
 * Class FieldsCollection
 * @package Symbiotic\Form
 * [
 * ]
 */
class FieldsGroup implements FieldInterface
{

    protected array $data = [
        'name' => '',
        'title' => '',
        'collapsed' => false,
        'fields' => [],
    ];

    protected string $template = 'fields/group';

    public function __construct(array $data)
    {

        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param FieldInterface $item
     * @return FieldInterface
     */
    public function add(FieldInterface $item): FieldInterface
    {
        $this->data['fields'][] = $item;
    }

    /**
     * @param $data
     */
    public function setValues(array $data)
    {
        $this->data['fields'] = (new FormBuilder())->setValues($this->data['fields'], $data);
    }


    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function isCollapsed(): bool
    {
        return (bool)$this->data['collapsed'];
    }


    /**
     * @return FieldInterface[]
     */
    public function getFields():array
    {
        return $this->data['fields'];
    }

    /**
     * @return FieldInterface[]
     */
    public function getFieldsArray():array
    {
        $fields = [];
        foreach ($this->data['fields'] as $field) {
            if($field instanceof FieldsGroup) {
                $fields = array_merge($fields, $field->getFields());
            } else {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function __toString()
    {
        $template = $this->template;
        if (false === \strpos($template, '::')) {
            $template = FormBuilder::getTemplatesPackageId() . '::' . $template;
        }
        return view($template, ['field' => $this])->fetch();
    }
}