<?php

namespace Symbiotic\Form;


use function _S\collect;
use function _S\view;

class Form implements FormInterface
{

    protected FormBuilder $helper;

    protected array $data = [
        'action' => '',
        'method' => 'post',
        'encode' => self::ENCTYPE_MULTIPART,
        'fields' => [],
        'attributes' => []
    ];

    protected $template = 'form/form';

    public function __construct(array $data = [], FormBuilder $formBuilder = null)
    {
        $this->helper = $formBuilder ? $formBuilder : new FormBuilder();
        if (!empty($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = $this->helper->fromArray($data['fields']);
        }

        $this->data = array_merge($this->data, $data);
    }

    public function setAction(string $action): FormInterface
    {
        $this->data['action'] = $action;

        return $this;
    }

    public function getAction():string
    {
        return $this->data['action'];
    }

    public function getMethod():string{
        return $this->data['method'];
    }

    public function setMethod(string $method): FormInterface
    {
        if(!in_array(strtolower($method),['get','post'])) {
            throw new \Exception(' Invalid method ['.$method.'], only get, post!');
        }

        $this->data['method'] = strtolower($method);
    }

    /**
     * @param $type
     * @param $data
     * @return FieldInterface
     */
    public function addField(string $type, array $data): FieldInterface
    {
        $field = $this->helper->createField($type, $data);
        $this->data['fields'][] = $field;

        return $field;
    }

    public function setValues(array $data)
    {
       $this->helper->setValues($this->data['fields'],$data);
    }

    /**
     * @return array
     * @todo Надо будет сделать на классах поля и отдавать их коллекцию
     * но пока и так сойдет)
     */
    public function getFields(): array
    {
        return $this->data['fields'];
    }

    public function getValidator(array $data = []): FieldsValidator
    {
        return new FieldsValidator($this->data['fields'], $data);
    }

    public function render($template = null)
    {
        if (!$template) {
            $template = $this->template;
            if (false === strpos($template, '::')) {
                $template = FormBuilder::getTemplatesPackageId() . '::' . $template;
            }
        }

        return view($template, ['form' => $this])->fetch();
    }

}