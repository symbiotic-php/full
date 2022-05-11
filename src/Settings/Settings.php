<?php

namespace Symbiotic\Settings;

use Symbiotic\Container\ArrayAccessTrait;
use Symbiotic\Container\ItemsContainerTrait;
use Symbiotic\Container\MultipleAccessTrait;

class Settings implements SettingsInterface
{
    use ItemsContainerTrait,
        ArrayAccessTrait,
        MultipleAccessTrait;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }

}