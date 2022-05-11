<?php

namespace Symbiotic\Settings;


use Psr\Http\Message\ServerRequestInterface;
use Symbiotic\Core\View\View;
use Symbiotic\Packages\PackageConfig;
use Symbiotic\Packages\PackagesRepository;
use function _S\settings;

abstract class PackageSettingsControllerAbstract
{
    /**
     * @var PackageConfig
     */
    protected PackageConfig $package;

    protected array $errors = [];

    /**
     * @var SettingsRepositoryInterface
     */
    protected SettingsRepositoryInterface $settings_repository;


    /**
     * PackageSettingsControllerAbstract constructor.
     * @param PackageConfig $package {@see PackagesRepository::getPackageConfig()}
     * @param SettingsRepositoryInterface $repository
     */
    public function __construct(PackageConfig $package, SettingsRepositoryInterface $repository)
    {
        $this->package = $package;
        $this->settings_repository = $repository;
    }

    /**
     * @return View
     */
    abstract public function edit(): View;

    /**
     * @param ServerRequestInterface $request
     * @return View
     *  * @throws \Exception
     */
    abstract public function save(ServerRequestInterface $request): View;


    protected function getPackageSettings(): SettingsInterface
    {
        return settings($this->package->getId());
    }

    protected function addError(string $field, string $message)
    {
        $this->errors['fields'][$field] = $message;
    }

    protected function validateData($data): bool
    {
        /** {@see addError()}*/
        return true;
    }


}