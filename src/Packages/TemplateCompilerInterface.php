<?php

namespace Symbiotic\Packages;

interface TemplateCompilerInterface
{

    public function compile(string $template): string;

    /**
     * @return array|string[] allowed extension
     */
    public function getExtensions(): array;
}