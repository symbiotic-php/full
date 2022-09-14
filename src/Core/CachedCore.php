<?php

declare(strict_types=1);

namespace Symbiotic\Core;

use Symbiotic\Container\{CachedContainerInterface, CachedContainerTrait};


class CachedCore extends Core implements CachedContainerInterface
{
    use CachedContainerTrait {
        CachedContainerTrait::unserialized as traitUnserialized;
        CachedContainerTrait::getSerializeData as traitGetSerializeData;
    }

    /**
     * @return array
     */
    public function getSerializeData(): array
    {
        $data = $this->traitGetSerializeData();
        $data['config'] = $this['bootstrap_config'];
        return $data;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function unserialized(array $data): void
    {
        $this->__construct($data['config']);
    }
}