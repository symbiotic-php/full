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
     * @return string строка кода php для выполнения в eval или сохранения в файл
     *
     * @see \Symbiotic\Packages\TemplateCompiler
     * @uses \Symbiotic\Packages\ResourcesRepositoryInterface::getResourceFileStream()
     *
     * @throws \Exception|ResourceExceptionInterface
     *
     */
    public function getTemplate(string $package_id, string $path): string;
}