<?php

namespace Dissonance\Packages;


/**
 * Interface TemplatesRepositoryInterface
 * @package Dissonance\Packages
 */
interface TemplatesRepositoryInterface
{


    /**
     * @param string $package_id
     * @param string $path
     *
     * @return string php code string for eval
     *
     * @see \Dissonance\Packages\TemplateCompiler
     * @uses \Dissonance\Packages\ResourcesRepositoryInterface::getResourceFileStream()
     *
     * @throws \Throwable
     *
     */
    public function getTemplate(string $package_id, string $path): string;
}