<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class Settlements implements SettlementsInterface
{

    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var array
     */
    protected array $find_patterns = [];

    /**
     * Settlements constructor.
     *
     * @param array $items    from config
     *                        [
     *                        [
     *                        'prefix' => '/backend/',
     *                        'router' => 'backend',
     *                        // optional params
     *                        'settings' => [],
     *                        'locale' => ''...
     *                        ],
     *                        [
     *                        'prefix' => '/module1_baseurl/',
     *                        'router' => 'module1',
     *                        // .....
     *                        ]
     *                        ];
     *
     *
     *
     *
     */
    public function __construct(protected SettlementFactory $factory, array $items)
    {
        $pattern = '';
        $index = 0;
        $counter = 0;

        foreach ($items as $v) {
            $pattern .= '(?<id_' . $index . '>^/' . preg_quote(trim($v['prefix'], '\\/'), '~') . '/.*)|';
            $this->items[$index++] = $v;
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
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public static function normalizePrefix(string $prefix): string
    {
        $prefix = trim($prefix, ' \\/');

        return $prefix == '' ? '/' : '/' . $prefix . '/';
    }

    /*  public function addSettlement(array $data)
      {
          /// parent::add($settlement = new Settlement($data));
          //  return $settlement;
      }*/

    /**
     * @param string $router
     *
     * @return SettlementInterface|null
     */
    public function getByRouter(string $router): ?SettlementInterface
    {
        return $this->getByKey('router', $router);
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
        $result = [];
        foreach ($this->items as $v) {
            if ($v->get($key) === $value) {
                if ($all) {
                    $result[] = $this->factory->make($v);
                } else {
                    return $this->factory->make($v);
                }
            }
        }
        return $all ? $result : null;
    }

    /**
     * @param string $url
     *
     * @return Settlement|null
     */
    public function getByUrl(string $url): ?SettlementInterface
    {
        $path = $this->getPathByUrl($url);


        foreach ($this->find_patterns as $find_pattern) {
            if (preg_match($find_pattern, $path, $m) === 1) {
                foreach ($m as $k => $v) {
                    if (is_int($k) || empty($v)) {
                        continue;
                    }
                    $id = substr($k, 3);
                    return $this->factory->make($this->items[$id]);
                }
            }
        }

        return null;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getPathByUrl(string $url): string
    {
        return preg_replace('~(^((.+?\..+?)[/])|(^(https?://)?localhost(:\d+)?[/]))(.*)~i', '/', $url);
    }
}