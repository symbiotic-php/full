<?php

namespace Symbiotic\Routing;


/**
 * Class Settlement
 * @package Symbiotic\Routing
 * @todo Добавить интефейс
 */
class Settlement
{
    protected array $config = [];

    protected string $path = '/';

    /**
     * Settlement constructor.
     *
     * @param array $config = [
     *      'prefix' => '/backend/', // Require parameter
     *      'router' => 'backend', // Require parameter
     *       // optional params
     *      'settings' => [],
     *      'locale' => ''...
     *    ];
     *
     *
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->path = '/' . trim($config['prefix'], '\\/ ');

    }

    public function getUriWithoutSettlement(string $uri): string
    {
        return preg_replace('/^' . preg_quote($this->getPath(), '/') . '/uDs', '', $uri);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRouter(): string
    {
        return $this->get('router');
    }

    /**
     * @param string|null $name
     * @param null $default
     *
     * @return array|mixed
     */
    public function get(string $name = null, $default = null)
    {
        return !$name ? $this->config : (isset($this->config[$name]) ? $this->config[$name] : $default);
    }

    /**
     * Проверяет соответствие пути к префиксу поселения
     * Например:
     * prefix = '/test/'
     * валидные пути
     * /test/
     * /test/data
     * /test/data/data....
     *
     * @param string $path
     *
     * @return bool
     */
    public function validatePath(string $path)
    {
        return (bool)preg_match('/^' . preg_quote($this->getPath(), '/') . '.*/uDs', $path, $r);
    }


}