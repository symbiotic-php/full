<?php

namespace Symbiotic\Form;


interface FieldsBuilderInterface
{


    const FILE = 'file';
    const HTML = 'html';
    const DATE = 'date';
    const EMAIL = 'email';
    const URL = 'url';
    const NUMBER = 'number';
    const BUTTON_SUBMIT = 'submit';
    const BUTTON_BUTTON = 'button';

    public function createField(string $type, array $data): FieldInterface;

    public function text(string $name, string $label, string $value = null): FillableInterface;

    public function file(string $name, string $label, string $value = null): FillableInterface;

    public function textarea(string $name, string $label, string $value = null): FillableInterface;

    public function hidden(string $name, string $value = null, array $attributes = []): FillableInterface;

    public function select(string $name, string $label, array $variants = [], string $value = null, array $attributes = []): FillableInterface;

    public function radio(string $name, string $label, array $variants = [], string $value = null, array $attributes = []): FillableInterface;

    public function checkbox(string $name, string $label, array $variants = [], array $values = []): FillableInterface;

    public function html(string $html): FieldInterface;

    public function button($type = 'button', string $content = null);

    public function submit(string $value = 'Send', array $attributes = []);

    public function group(string $title, string $name = null, array $fields = []): FieldInterface;


}