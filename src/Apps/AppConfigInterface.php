<?php

namespace Dissonance\Apps;

use Dissonance\Container\ArrayContainerInterface;

/**
 * Interface AppConfigInterface
 *
 * @package dissonance/apps-contracts
 */
interface AppConfigInterface extends ArrayContainerInterface
{
    /**
     * The app ID is based on its alias and parent ID
     * @return string
     * @see \Dissonance\Apps\AppsRepository::addApp();
     */
    public function getId(): string;

    public function getAppName(): string;

    /**
     * @return string|null
     * @uses \Dissonance\Routing\AppRouting
     */
    public function getRoutingProvider(): ?string;

    public function hasParentApp(): bool;

    public function getParentAppId(): ?string;

}