<?php

namespace Symbiotic\Settings;


interface SettingsFormInterface
{
    /**
     * @key type in field
     */
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const SELECT = 'select';
    const RADIO = 'radio';
    const CHECKBOX = 'checkbox';
    const BOOL = 'bool';
    const PASSWORD = 'password';

    /**
     * группа полей в которой может находится коллекция
     */
    const GROUP = 'group';


    /**
     * @return array
     * @todo Надо будет сделать на классах поля и отдавать их коллекцию
     * но пока и так сойдет)
     */
   public function getFields():array;
}