<?php

namespace Dissonance\Routing;


/**
 * Class SettlementFactory
 *
 * для расширения Поселений через DI  оберните данный класс
 * @see \Dissonance\Container\DIContainerInterface::extend()
 *
 * @package Dissonance\Routing
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