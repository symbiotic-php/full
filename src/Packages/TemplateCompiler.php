<?php

namespace Symbiotic\Packages;

use Symbiotic\Mimetypes\MimeTypesMini;

class TemplateCompiler {

    protected $extensions = [];

    /**
     * @param TemplateCompilerInterface $compiler
     */
    public function addCompiler(TemplateCompilerInterface $compiler)
    {
        // todo: нужно сделать по именам
        foreach ($compiler->getExtensions() as $v)
        {
            $this->extensions[$v] = $compiler;
        }
    }

    /**
     * @param string $path путь к файлу или его название для определения компилера
     * @param string $template контент файла для преобразования
     *
     * @return string  html / php валидный код для выполнения через include {@link https://www.php.net/manual/ru/function.include.php)
     */
    public function compile(string $path, string $template):string
    {
        $ext = (new MimeTypesMini())->findExtension($path, array_keys($this->extensions));
        return ($ext!==false) ?  $this->extensions[$ext]->compile($template):$template;



    }
}