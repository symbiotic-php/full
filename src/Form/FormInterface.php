<?php

namespace Symbiotic\Form;


interface FormInterface
{

  const ENCTYPE_URL = 'application/x-www-form-urlencoded';

  const ENCTYPE_MULTIPART = 'multipart/form-data';

    /**
     * @return array
     * @todo Надо будет сделать на классах поля и отдавать их коллекцию
     * но пока и так сойдет)
     */
   public function getFields():array;

    public function addField(string $type, array $data): FieldInterface;

   public function getValidator(array $data ):FieldsValidator;

    public function setAction(string $action): FormInterface;

    public function getAction():string;

    public function getMethod():string;

    public function setMethod(string $method): FormInterface;

}