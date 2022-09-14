<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


interface SettlementInterface
{
    /**
     * @param string $uri
     *
     * @return string
     */
    public function getUriWithoutSettlement(string $uri): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getRouter(): string;

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Checks the correspondence of the path to the settlement prefix
     *
     * For example:
     * prefix = '/test/'
     * valid paths:
     * /test/
     * /test/data
     * /test/data/data....
     *
     * @param string $path
     *
     * @return bool
     */
    public function validatePath(string $path): bool;
}