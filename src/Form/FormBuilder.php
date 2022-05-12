<?php

namespace Symbiotic\Form;


use Symbiotic\Form\Fields\{Boolean, Button, Checkbox, Input, Radio, Select};
use function _S\collect;

/**
 * Class FormHelper
 * @package Symbiotic\Form
 *
 * To add fields, subscribe to an event in the event('FieldTypesRepository::class','\My\HandlerObject') kernel
 */
class FormBuilder
{
    protected static ?array $types = null;

    protected static string $templatesPackageId = 'ui_form';


    public function __construct()
    {
        // crutch for symbiotic field types. I did not add it to bootstrap, the fields are needed only in the admin panel
        if (null === static::$types) {
            static::$types = [
                'input' => Input::class,
                'select' => Select::class,
                'radio' => Radio::class,
                'checkbox' => Checkbox::class,
                'button' => Button::class,
                'bool' => Boolean::class,
                /** and virtual input types {@see createField()} **/
            ];

            if (function_exists('_S\\event')) {
                \_S\event($this);
            }
        }
    }

    public static function setTemplatesPackageId(string $package_id)
    {
        static::$templatesPackageId = $package_id;
    }

    public static function getTemplatesPackageId(): string
    {
        return static::$templatesPackageId;
    }

    /**
     * @param string $type the field type must include the application prefix: filesystems::path
     * @param string $class className implements {@see FieldInterface, FillableInterface}
     */
    public function addType(string $type, string $class)
    {
        static::$types[$type] = $class;
    }

    public function text(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::TEXT, $data);
    }

    /**
     * @param string $type
     * @param array $data = [
     *     'name' => 'string',
     *     'label' => 'string',
     *     'value' => null,
     *     'attributes' => [],
     *     'validators' => [],
     * ]
     * @return FieldInterface
     * @throws \Exception
     */
    public function createField(string $type, array $data): FieldInterface
    {
        $types = static::$types;


        if (isset($types[$type])) {
            $class = $types[$type];
            return new $class($data);
        } elseif (in_array($type, ['text', 'hidden', 'file', 'number', 'submit', 'password', 'url', 'date', 'email'])) {
            $class = $types['input'];
            if (!isset($data['attributes'])) {
                $data['attributes'] = [];
            }
            $data['attributes']['type'] = $type;
            return new $class($data);

        }
        throw  new \Exception('Field type [' . $type . '] not found!');
    }

    /**
     * @return array|FieldInterface[]
     */
    public function fromArray(array $fields): array
    {
        if (!empty($fields) && is_array($fields)) {
            foreach ($fields as &$v) {
                if (is_array($v)) {
                    $v = $this->createField($v['type'], $v);
                }
            }
            unset($v);
        }
        return $fields;
    }


    public function setValues(array $fields, array $data)
    {
        $data = collect($data);
        foreach ($fields as $v) {
            if($v instanceof FillableInterface) {
                $name = $v->getDotName();
                $v->setValue($data->get($name));
            } elseif ($v instanceof FieldsGroup) {
                $v->setValues($data);
            }
        }

        return $fields;
    }

    public function textarea(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::TEXTAREA, $data);
    }

    public function select(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::SELECT, $data);
    }

    public function radio(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::RADIO, $data);
    }

    public function checkbox(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::CHECKBOX, $data);
    }

    public function hidden(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::HIDDEN, $data);
    }

    public function file(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::FILE, $data);
    }

    public function group(string $title, string $name = null, array $fields = []): FieldInterface
    {
        return $this->createField(FieldInterface::GROUP, compact('title', 'name', 'fields'));
    }

    public function submit(array $data = []): FieldInterface
    {
        return $this->createField(FieldInterface::GROUP, $data);
    }

}