<?php

namespace Dissonance\Apps;

use Dissonance\Container\{ArrayAccessTrait, ItemsContainerTrait};

/**
 * Class AppConfig
 * @package Dissonance\Apps
 * @deprecated  не нужная прослойка, все есть в самом приложении
 */
class AppConfig implements AppConfigInterface
{
    use ArrayAccessTrait,
        ItemsContainerTrait;

    /**
     * @var string
     */
    protected $id = null;

    public function __construct(array $config)
    {
        $this->id = $config['id'] ?? null;
        $this->items = $config;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->has('name')?$this->get('name'):ucfirst($this->getId());
    }

    /**
     * @return string|null
     */
    public function getRoutingProvider(): ?string
    {
        return $this->get('routing');
    }

    /**
     * @return bool
     */
    public function hasParentApp(): bool
    {
        return $this->has('parent_app');
    }

    /**
     * @return string|null
     */
    public function getParentAppId(): ?string
    {
        return $this->get('parent_app');
    }


}