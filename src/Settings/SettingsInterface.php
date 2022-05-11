<?php

namespace Symbiotic\Settings;


use Symbiotic\Container\BaseContainerInterface;
use Symbiotic\Container\MultipleAccessInterface;

interface SettingsInterface extends BaseContainerInterface, MultipleAccessInterface,\ArrayAccess
{
    public function all(): array;
}