<?php

namespace Symbiotic\Packages;


/**
 * Interface TemplatesRepositoryInterface
 * @package Symbiotic\Packages
 */
interface TemplatesRepositoryInterface
{


    /**
     * @param string $package_id
     * @param string $path
     *
     * @return string php code string for eval
     *
     * @see \Symbiotic\Packages\TemplateCompiler
     * @uses \Symbiotic\Packages\ResourcesRepositoryInterface::getResourceFileStream()
     *
     * @throws \Throwable
     *
     */
    public function getTemplate(string $package_id, string $path): string;
}