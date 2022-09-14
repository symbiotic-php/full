<?php

declare(strict_types=1);

namespace Symbiotic\Apps;

use Psr\Container\ContainerInterface;
use Symbiotic\Container\DIContainerInterface;


class AppsCloningRepository implements AppsRepositoryInterface
{
    protected array $instances = [];

    public function __construct(
        protected AppsRepositoryInterface $appsRepository,
        protected DIContainerInterface $container
    ) {
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function enabled(): array
    {
        return $this->appsRepository->enabled();
    }

    /**
     * @inheritDoc
     *
     * @param array $ids
     *
     * @return void
     */
    public function disableApps(array $ids): void
    {
        $this->appsRepository->disableApps($ids);
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function all(): array
    {
        return $this->appsRepository->all();
    }


    /**
     * @inheritDoc
     *
     * @return array|string[]
     */
    public function getIds(): array
    {
        return $this->appsRepository->getIds();
    }

    /**
     * @inheritDoc
     *
     * @param string $id
     *
     * @return AppConfigInterface|null
     */
    public function getConfig(string $id): ?AppConfigInterface
    {
        return $this->appsRepository->getConfig($id);
    }

    /**
     * @inheritDoc
     *
     * @param string $id
     *
     * @return ApplicationInterface
     * @throws \Exception
     */
    public function get(string $id): ApplicationInterface
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        $app = $this->appsRepository->get($id);

        return $this->instances[$id] = $app->cloneInstance($this->container);
    }

    /**
     * @inheritDoc
     *
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->appsRepository->has($id);
    }

    /**
     * @inheritDoc
     *
     * @param string $id
     *
     * @return ApplicationInterface
     * @throws \Exception
     */
    public function getBootedApp(string $id): ApplicationInterface
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id]->bootstrap();
        }
        // We take the original downloaded application so as not to repeat the initialization
        return $this->instances[$id] = $this->appsRepository->getBootedApp($id)
            ->cloneInstance($this->container);
    }

    /**
     * @inheritDoc
     *
     * @param string $app_id
     *
     * @return array
     */
    public function getPluginsIds(string $app_id): array
    {
        return $this->appsRepository->getPluginsIds($app_id);
    }

    /**
     * @inheritDoc
     *
     * @param array $config
     *
     * @return mixed|void
     */
    public function addApp(array $config): void
    {
        $this->appsRepository->addApp($config);
    }

    /**
     * @param ContainerInterface|null $container
     *
     * @return object|null
     */
    public function cloneInstance(?ContainerInterface $container): ?AppsRepositoryInterface
    {
        $new = clone $this;
        $new->instances = [];
        /**
         * @var DIContainerInterface $container
         */
        $new->container = $container;

        return $new;
    }


    /**
     * @return void
     */
    public function flush(): void
    {
        $this->instances = [];
        $this->appsRepository->flush();
    }
}