<?php

namespace Dissonance\Core;

use Dissonance\Container\{CachedContainerInterface,CachedContainerTrait};

class CachedCore extends Core implements CachedContainerInterface
{
    use CachedContainerTrait {
        CachedContainerTrait::unserialized as traitUnserialized;
        CachedContainerTrait::getSerializeData as traitGetSerializeData;
    }

    public function getSerializeData():array
    {
       $data = $this->traitGetSerializeData();
       $data['config'] = $this['bootstrap_config'];
       return $data;
    }

    protected function unserialized(array $data)
    {
        $this->__construct($data['config']);
    }
}