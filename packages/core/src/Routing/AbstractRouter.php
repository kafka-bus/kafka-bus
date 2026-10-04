<?php

declare(strict_types=1);

namespace KafkaBus\Core\Routing;

use Throwable;

/**
 * @template TRoute of RouteInterface
 * @template TResolved
 */
abstract class AbstractRouter
{
    /**
     * @var array<string, TResolved>
     */
    private array $resolved = [];

    /**
     * @param RouteCollection<TRoute> $routes
     */
    public function __construct(
        private readonly RouteCollection $routes,
    ) {
    }

    /**
     * @return list<TRoute>
     */
    public function routes(): array
    {
        return $this->routes->all();
    }

    /**
     * @param string $key
     * @return TResolved
     *
     * @throws Throwable
     */
    protected function resolve(string $key): mixed
    {
        return $this->resolved[$key] ??= $this->build($key, $this->routeOrFail($key));
    }

    /**
     * @param string $key
     * @return TRoute
     *
     * @throws Throwable
     */
    private function routeOrFail(string $key): mixed
    {
        return $this->routes->get($key) ?? throw $this->routeNotFound($key);
    }

    /**
     * @param TRoute $route
     * @return TResolved
     */
    abstract protected function build(string $key, mixed $route): mixed;

    abstract protected function routeNotFound(string $key): Throwable;
}
