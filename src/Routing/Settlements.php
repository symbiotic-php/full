<?php

namespace Symbiotic\Routing;

use Symbiotic\Core\Support\Collection;

/**
 * Class Settlements
 * @package Symbiotic\Services
 *

 */
class Settlements implements SettlementsInterface
{

    protected array $items = [];

    protected array $find_patterns = [];

    /**
     * Settlements constructor.
     *
     * @param \Closure $items  Generator for Foreach
     * [
     *    [
     *      'prefix' => '/backend/',
     *      'router' => 'backend',
     *       // optional params
     *      'settings' => [],
     *      'locale' => ''...
     *    ],
     *    [
     *      'prefix' => '/api/',
     *      'router' => 'api',
     *       // .....
     *    ],
     *    [
     *      'prefix' => '/module1_baseurl/',
     *      'router' => 'module1',
     *       // .....
     *    ]
     * ];
     *
     *
     *
     *
     */
    public function __construct(\Closure $items, SettlementFactory $factory)
    {
        $pattern = '';
        $index = 0;
        $counter = 0;

        foreach ($items() as $v) {
            $pattern .= '(?<id_' . $index . '>^/' . preg_quote(trim($v['prefix'], '\\/'), '~') . '/.*)|';
            // Может оставить массив, а при запросе оборачивать? как часто будут запрашивать?
            $this->items[$index++] = is_array($v) ? new Settlement($v) : $v;
            if ($counter === 60) {
                $this->find_patterns[] = '~' . rtrim($pattern, '|') . '~';
                $pattern = '';
                $counter = 0;
            } else {
                $counter++;
            }
        }
        if ($counter > 0) {
            $this->find_patterns[] = '~' . rtrim($pattern, '|') . '~';
        }

        //  var_dump($this);
        //  exit;
        //  exit;
    }

    public static function normalizePrefix(string $prefix): string
    {
        $prefix = trim($prefix, ' \\/');

        return $prefix == '' ? '/' : '/' . $prefix . '/';
    }

    public function addSettlement(array $data)
    {
        /// parent::add($settlement = new Settlement($data));
        //  return $settlement;
    }

    public function getByRouter(string $router)
    {
        return $this->getByKey('router', $router);
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $all
     * @return Collection|Settlement[]|Settlement|null
     */
    public function getByKey(string $key, $value, $all = false)
    {
        $result = [];
        foreach ($this->items as $v) {
            if ($v->get($key) === $value) {
                if ($all) {
                    $result[] = $v;
                } else {
                    return $v;
                }
            }
        }
        return $result;
    }

    public function getByUrl(string $url): ?Settlement
    {
        $path = $this->getPathByUrl($url);


        foreach ($this->find_patterns as $find_pattern) {
            if (preg_match($find_pattern, $path, $m) === 1) {
                foreach ($m as $k => $v) {
                    if (is_int($k) || empty($v)) {
                        continue;
                    }
                    $id = substr($k, 3);
                    return $this->items[$id];
                }
            }
        }

        return null;
    }

    public function getPathByUrl(string $url): string
    {
        return preg_replace('~(^((.+?\..+?)[/])|(^(https?://)?localhost(:\d+)?[/]))(.*)~i', '/', $url);
    }
}