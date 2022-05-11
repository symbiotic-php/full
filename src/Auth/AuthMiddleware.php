<?php

namespace Symbiotic\Auth;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Apps\AppConfigInterface;
use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\Routing\RouteInterface;
use function _S\response;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RouteInterface
     */
    protected RouteInterface $route;

    public function __construct(ContainerInterface $container, RouteInterface $route)
    {
        $this->container = $container;
        $this->route = $route;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface|\Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $backend_prefix = $this->container->get('config')->get('backend_prefix', 'backend');
        if (empty($backend_prefix)) {
            throw new \Exception('Not configured Backend!');
        }
        // Проверяем что запрошен роут админки
        if (preg_match('/^\/' . preg_quote(trim($backend_prefix, "\\/"), '/') . '.*/uDs', $path, $r)) {

            $route = $this->route;
            $action = $route->getAction();
            $app_id = $action['app'] ?? null;
            /**
             * @var AppsRepositoryInterface $apps_repository
             * @var AppConfigInterface|null $app_config
             */
            $apps_repository = $this->container->get(AppsRepositoryInterface::class);
            $app_config = $apps_repository->getConfig($app_id);
            // роут без приложения не может быть в админке
            if (empty($app_id) || !($app_config instanceof AppConfigInterface)) {
                return response(403);// todo add message
            }
            /**
             * @var AuthServiceInterface $auth
             */
            $auth = $this->container[AuthServiceInterface::class];
            if (null === $auth->getIdentity()) {
                $auth->authenticate();
            }
            $user = $auth->getIdentity();
            if ($user instanceof UserInterface) {
                $group = $user->getAccessGroup();
                $app_access = $app_config->get('auth_access_group');
                if (
                    ($app_access === 'admin' && $group === UserInterface::GROUP_ADMIN)
                    || ($app_access !== 'admin' && ($group === UserInterface::GROUP_MANAGER || $group === UserInterface::GROUP_ADMIN))
                ) {
                    return $handler->handle($request);
                } else {
                    return response(403);// todo add message
                }
            }
            return response(403);// todo add message
        } else {
            return $handler->handle($request);
        }

    }

}