<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class Settlement implements SettlementInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected string $path = '/';

    /**
     * Settlement constructor.
     *
     * @param array $config = [
     *                      'prefix' => '/backend/', // Require parameter
     *                      'router' => 'backend', // Require parameter
     *                      // optional params
     *                      'settings' => [],
     *                      'locale' => ''...
     *                      ];
     *
     *
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->path = '/' . trim($config['prefix'], '\\/ ');
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function getUriWithoutSettlement(string $uri): string
    {
        return preg_replace('/^' . preg_quote($this->getPath(), '/') . '/uDs', '', $uri);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getRouter(): string
    {
        return $this->get('router');
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->config[$name] ?? $default;
    }

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
    public function validatePath(string $path): bool
    {
        return (bool)preg_match('/^' . preg_quote($this->getPath(), '/') . '.*/uDs', $path);
    }
}