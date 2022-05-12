<?php

namespace Symbiotic\Form;


use Symbiotic\Core\Support\Collection;
use function _S\collect;

class FieldsValidator
{

    protected $fields = [];

    /**
     * @var Collection
     */
    protected $data = null;

    protected $errors = [];

    public function __construct(array $fields, array $data = [])
    {
        $this->fields = $fields;
        $this->setData($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data):FieldsValidator
    {
        $this->data = collect($data);

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors():array
    {
        return $this->errors;
    }

    public function validate(): bool
    {

        $fillable = [];
        foreach ($this->fields as $v) {
            if($v instanceof FillableInterface) {
                $fillable[$v->getDotName()] = $v;
            } elseif ($v instanceof FieldsGroup) {
                $fillable = array_merge($fillable, $v->getFieldsArray());
            }
        }
        foreach ($fillable as $field) {
            $value = $this->data->get($field->getDotName());
            if(!$field->validate($value)) {
                $this->errors[$field->getDotName()] = $field->getError();
            }
        }

        return empty($this->errors);
    }

}