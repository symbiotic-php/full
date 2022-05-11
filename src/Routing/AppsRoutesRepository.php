<?php


namespace Symbiotic\Routing;


class AppsRoutesRepository
{
    /**
     * @var AppRoutingInterface[]
     */
    protected array $providers = [];

    public function append(AppRoutingInterface $routing)
    {
       $this->providers[$routing->getAppId()] = $routing;
    }

    public function getByAppId($app_id)
    {
        return $this->providers[$app_id]??null;
    }
    /**
     * @return array|AppRoutingInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
}