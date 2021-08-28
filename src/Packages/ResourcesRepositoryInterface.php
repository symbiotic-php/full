<?php

namespace Symbiotic\Packages;

use Psr\Http\Message\StreamInterface;

/**
 * Interface ResourcesRepositoryInterface
 * @package Symbiotic\Packages
 */
interface ResourcesRepositoryInterface
{

    /**
     * @param string $package_id
     * @param string $path
     * @return StreamInterface
     * @throws \Throwable  Если файл не найден
     */
    public function getResourceFileStream(string $package_id, string $path): StreamInterface;
}