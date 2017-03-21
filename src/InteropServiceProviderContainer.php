<?php

namespace Basal\InteropServiceProvider\LeagueContainer;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Interop\Container\ServiceProviderInterface;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Container\ServiceProvider\ServiceProviderInterface as LeagueServiceProviderInterface;

/**
 * Class InteropServiceProviderContainer.
 */
final class InteropServiceProviderContainer implements ContainerInterface
{
    /** @var Container */
    private $container;

    /**
     * InteropServiceProviderContainer constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add an interop or League service provider to the container.
     *
     * @param ServiceProviderInterface|LeagueServiceProviderInterface|string $provider
     *
     * @return $this
     */
    public function addServiceProvider($provider)
    {
        if ($provider instanceof ServiceProviderInterface) {
            foreach ($provider->getServices() as $service => $callable) {
                if ($this->hasShared($service)) {
                    $share = function () use ($callable) {
                        return $callable($this);
                    };
                } else {
                    $previous = $this->get($service);
                    $share = function () use ($callable, $previous) {
                        return $callable($this, function () use ($previous) {
                            return $previous;
                        });
                    };
                }

                $this->share($service, $share);
            }

            return $this;
        }

        $this->container->addServiceProvider($provider);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add($alias, $concrete = null, $share = false)
    {
        return $this->container->add($alias, $concrete, $share);
    }

    /**
     * @inheritDoc
     */
    public function share($alias, $concrete = null)
    {
        return $this->container->share($alias, $concrete);
    }

    /**
     * @inheritDoc
     */
    public function extend($alias)
    {
        return $this->container->extend($alias);
    }

    /**
     * @inheritDoc
     */
    public function inflector($type, callable $callback = null)
    {
        return $this->container->inflector($type, $callback);
    }

    /**
     * @inheritDoc
     */
    public function call(callable $callable, array $args = [])
    {
        return $this->container->call($callable, $args);
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @inheritDoc
     */
    public function hasShared($alias, $resolved = false)
    {
        return $this->container->hasShared($alias, $resolved);
    }

    /**
     * @inheritdoc
     */
    public function delegate(InteropContainerInterface $container)
    {
        $this->container->delegate($container);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasInDelegate($alias)
    {
        return $this->container->hasInDelegate($alias);
    }
}
