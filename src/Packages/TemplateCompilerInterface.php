<?php

namespace Symbiotic\Packages;


interface TemplateCompilerInterface
{

    /**
     * @param string $template
     * @return string
     * @throws CompileExceptionInterface
     */
    public function compile(string $template): string;

    /**
     * @return string[] allowed extension
     */
    public function getExtensions(): array;
}