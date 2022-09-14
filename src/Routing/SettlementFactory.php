<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


/**
 * Class SettlementFactory
 *
 * To extend Settlements via YOU, wrap this class via extend
 * @see     \Symbiotic\Container\DIContainerInterface::extend()
 *
 * @package Symbiotic\Routing
 */
class SettlementFactory
{
    /**
     * @param array $parameters
     *
     * @return Settlement
     */
    public function make(array $parameters = []): SettlementInterface
    {
        return new Settlement($parameters);
    }
}