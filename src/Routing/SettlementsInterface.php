<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


interface SettlementsInterface
{
    /**
     * @param string $router
     *
     * @return SettlementInterface|null
     */
    public function getByRouter(string $router): ?SettlementInterface;

    /**
     * @param string $url
     *
     * @return SettlementInterface|null
     */
    public function getByUrl(string $url): ?SettlementInterface;

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $all
     *
     * @return SettlementInterface[]|SettlementInterface|null
     */
    public function getByKey(string $key, mixed $value, bool $all = false): SettlementInterface|array|null;
}