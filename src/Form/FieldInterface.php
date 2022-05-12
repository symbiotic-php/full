<?php

namespace Symbiotic\Form;


interface FieldInterface extends \JsonSerializable/*, \Stringable*/
{

    const TEXT = 'text';
    const HIDDEN = 'hidden';
    const PASSWORD = 'password';
    const FILE = 'file';
    const DATE = 'date';
    const EMAIL = 'email';
    const URL = 'url';
    const NUMBER = 'number';
    const SUBMIT = 'submit';

    const TEXTAREA = 'textarea';
    const SELECT = 'select';
    const RADIO = 'radio';
    const CHECKBOX = 'checkbox';

    const HTML = 'html';
    const BUTTON = 'button';

    const GROUP = 'group';


    public function __toString();
}