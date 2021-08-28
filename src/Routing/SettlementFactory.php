<?php

namespace Symbiotic\Routing;


/**
 * Class SettlementFactory
 *
 * для расширения Поселений через DI  оберните данный класс
 * @see \Symbiotic\Container\DIContainerInterface::extend()
 *
 * @package Symbiotic\Routing
 */
class SettlementFactory
{
    /**
     * @param array $parameters
     * @return Settlement
     */
    public function make(array $parameters = [])
    {
       return new Settlement($parameters);
    }
}