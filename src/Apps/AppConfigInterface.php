<?php

namespace Symbiotic\Apps;

use Symbiotic\Container\ArrayContainerInterface;

/**
 * Interface AppConfigInterface
 *
 * @package symbiotic/apps-contracts
 */
interface AppConfigInterface extends ArrayContainerInterface
{
    /**
     * The app ID is based on its alias and parent ID
     * @return string
     * @see \Symbiotic\Apps\AppsRepository::addApp();
     */
    public function getId(): string;

    public function getAppName(): string;

    /**
     * @return string|null
     * @uses \Symbiotic\Routing\AppRouting
     */
    public function getRoutingProvider(): ?string;

    public function hasParentApp(): bool;

    public function getParentAppId(): ?string;

}