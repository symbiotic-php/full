<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class PackagesSettlements implements SettlementsInterface
{
    /**
     * @var string
     */
    protected string $backend_prefix;

    /**
     * @param SettlementsInterface $settlements
     * @param AppsRoutesRepository $apps_routing
     * @param SettlementFactory    $factory
     * @param string               $backend_prefix
     */
    public function __construct(
        protected SettlementsInterface $settlements,
        protected AppsRoutesRepository $apps_routing,
        protected SettlementFactory $factory,
        string $backend_prefix
    ) {
        $this->backend_prefix = trim($backend_prefix, "\\/");
    }


    /**
     * @param string $router
     *
     * @return SettlementInterface|null
     */
    public function getByRouter(string $router): ?SettlementInterface
    {
        return $this->getSettlementByString($router);
    }

    /**
     * @param string $url
     *
     * @return SettlementInterface|null
     */
    public function getByUrl(string $url): ?SettlementInterface
    {
        $path = $this->getPathByUrl($url);
        $settlement = $this->getSettlementByString($path);
        if(null !== $settlement) {
            return $settlement;
        }
        return $this->settlements->getByUrl($url);
    }

    protected function getSettlementByString(string $string): ?SettlementInterface
    {
        if (preg_match(
            '~^(backend|api|default):([\da-z_\-\.]+)|/(' . preg_quote($this->backend_prefix, '~') . '|api)/(.[^/]+)~',
            $string,
            $m
        )) {
            if (!empty($m[1]) && !empty($m[2])) {
                $router = $m[1];
                $app_id = $m[2];
            } else {
                $router = $m[3];
                $app_id = $m[4];
            }
            if ($this->apps_routing->has($app_id)) {
                return $this->factory->make(
                    [
                        'prefix' => '/' . ($router === 'backend' ? $this->backend_prefix : $router) . '/' . $app_id . '/',
                        'router' => ($router === $this->backend_prefix ? 'backend' : $router) . ':' . $app_id
                    ]
                );
            }
        }

        $app_id = explode('/', ltrim($string, '\\/'))[0];
        if (!empty($app_id) && $this->apps_routing->has($app_id)) {
            return $this->factory->make(
                [
                    'prefix' => $app_id,
                    'router' => $app_id
                ]
            );
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $all
     *
     * @return SettlementInterface|array|SettlementInterface[]|null
     */
    public function getByKey(string $key, mixed $value, bool $all = false): SettlementInterface|array|null
    {
        if ($key === 'router') {
            if ($this->apps_routing->has($value)) {
                return $this->factory->make(
                    [
                        'prefix' => $value,
                        'router' => $value
                    ]
                );
            }
        }

        return $this->settlements->getByKey($key, $value, $all);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getPathByUrl(string $url): string
    {
       return  parse_url($url, PHP_URL_PATH);
    }
}