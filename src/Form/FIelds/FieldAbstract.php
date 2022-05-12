<?php

namespace Symbiotic\Form\Fields;


use Symbiotic\Form\FillableInterface;
use Symbiotic\Form\FormBuilder;
use Symbiotic\Form\Validator;
use function _S\view;

class FieldAbstract implements FillableInterface
{

    protected string $template = '';

    protected array $data = [
        'label' => '',
        'description' => '',
        'name' => '',
        'meta' => [],
        'value' => null,
        'default' => null,
        'placeholder' => '',
        'attributes' => [],
        'validators' => [],
        'error' => null,
    ];


    public function __construct(array $data = [])
    {
        $this->data = array_merge($this->data, $data);
    }


    public function setName(string $name): FillableInterface
    {
        $this->data['name'] = $name;
    }

    public function setLabel(string $label = ''): FillableInterface
    {
        $this->data['label'] = $label;
    }

    /**
     * @param array|string $value
     * @return $this
     */
    public function setValue($value): FillableInterface
    {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * @param string $description
     * @return FillableInterface
     */
    public function setDescription(string $description): FillableInterface
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * @param array|string $default
     * @return $this
     */
    public function setDefault($default): FillableInterface
    {
        $this->data['default'] = $default;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function setAttribute(string $key, $value)
    {
        $this->data['attributes'][$key] = $value;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->data['label'];
    }

    /**
     * @return array|mixed|string|null
     */
    public function getValue()
    {
        return $this->data['value'];
    }

    public function getDotName(): string
    {
        return trim(\str_replace(['][', ']', '['], ['.', '.', '.'], $this->data['name']), '.');
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDefault()
    {
        return $this->data['default'];
    }

    public function getDescription(): string
    {
        return $this->data['description'];
    }

    public function getAttributesHtml(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $name => $value) {
            // TODO: test and fix js attributes with code
            $attributes[] = \htmlspecialchars($name) . '="' . \htmlspecialchars($value) . '"';
        }
        return \implode(' ', $attributes);
    }


    public function getAttributes(): array
    {
        return $this->data['attributes'];
    }


    public function getValidators(): array
    {
        return $this->data['validators'];
    }

    /**
     * @param string|array $value
     * @return bool
     */
    public function validate($value): bool
    {
        /**
         * @var Validator $validator
         */
        foreach ($this->data['validators'] as $validator) {
            if (!$validator->validate($value)) {
                $this->data['error'] .= $validator->getError();
                return false;
                // todo: array errors...
            }
        }
        return true;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->data['error'];
    }

    public function placeholder(string $val): FillableInterface
    {
        $this->data['placeholder'] = $val;
    }

    public function getPlaceholder(): string
    {
        return $this->data['placeholder'];
    }


    public function jsonSerialize()
    {
        return array_merge($this->data, ['template' => $this->template]);
    }

    public function __toString()
    {

        return $this->render();
    }

    public function render($template = null)
    {
        if (!$template) {
            $template = $this->template;
            if (false === strpos($template, '::')) {
                $template = FormBuilder::getTemplatesPackageId() . '::' . $template;
            }
        }

        return view($template, ['field' => $this])->fetch();
    }
}