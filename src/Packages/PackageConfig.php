<?php

namespace Symbiotic\Packages;

use Symbiotic\Container\{ArrayAccessTrait, ArrayContainerInterface, ItemsContainerTrait};

/**
 * Class PackageConfig
 * @package Symbiotic\Packages
 */
class PackageConfig implements ArrayContainerInterface
{
    use ArrayAccessTrait,
        ItemsContainerTrait;


    /**
     * @property $items = [
     *     'id' => '',
     *     'bootstrappers' => [],
     *     'app' => [],
     *     'settings_form' => '\Pack\FormClassName',
     *     // or для сложных настроек {@uses \Symbiotic\Settings\PackageSettingsControllerAbstract}
     *     'settings_controller' => '\PAck\MySettingsController',
     *     // or
     *    'settings' => [
     *         ['field_name' => 'name', 'type' => 1 ], // {@see \Symbiotic\Form\FormInterface}
     *     ]
     *     ....
     * ]
     */
    /**
     * PackageConfig constructor.
     * @param array|\ArrayAccess $package_config
     */


    public function __construct(array|\ArrayAccess $package_config)
    {
        $this->items = $package_config;
    }

    public function getId(): string
    {
        return $this->get('id');
    }

    /**
     * @return array|null {@see \Symbiotic\Apps\AppConfigInterface wrapper array config}
     */
    public function getAppData(): ?array
    {
        return $this->get('app');
    }

    /**
     * get path with root package base path
     *
     * @param string $path
     * @return string|null
     */
    public function getPath(string $path): ?string
    {
        return \rtrim($this->get('base_path'), '\\/') . \_S\DS . \ltrim($path, '\\/');
    }


}