<?php

declare(strict_types=1);

namespace KafkaBus\Core\Routing;

use InvalidArgumentException;

/**
 * @template TRoute of RouteInterface
 */
final class RouteCollection
{
    /**
     * @var array<string, TRoute|LazyRoute<TRoute>>
     */
    private array $routes = [];

    /**
     * @param class-string<TRoute> $routeClass
     */
    public function __construct(
        private string $routeClass
    ) {
    }

    /**
     * @param TRoute|LazyRoute<TRoute> $route
     * @return $this
     */
    public function add(mixed $route): self
    {
        if (! $route instanceof $this->routeClass && ! $route instanceof LazyRoute) {
            throw new InvalidArgumentException(\sprintf(
                'Route must be an instance of [%s] or [%s], [%s] given.',
                $this->routeClass,
                LazyRoute::class,
                get_debug_type($route),
            ));
        }

        $this->routes[$route->key()] = $route;

        return $this;
    }

    /**
     * @return TRoute|null
     */
    public function get(string $key): mixed
    {
        $route = $this->routes[$key] ?? null;

        return $route === null ? null : self::unwrap($route);
    }

    /**
     * @return list<TRoute>
     */
    public function all(): array
    {
        return array_map(self::unwrap(...), array_values($this->routes));
    }

    /**
     * PHPStan cannot prove that excluding `LazyRoute` from `TRoute|LazyRoute<TRoute>`
     * leaves exactly `TRoute` (the template bound is `RouteInterface`, which
     * `LazyRoute` itself also implements), even though `RouteCollection` is only ever
     * instantiated with a concrete domain `Route` class as `TRoute`, never with
     * `LazyRoute` itself. Hence the explicit annotation below.
     *
     * @param TRoute|LazyRoute<TRoute> $route
     * @return TRoute
     */
    private static function unwrap(RouteInterface $route): RouteInterface
    {
        /** @var TRoute $resolved */
        $resolved = $route instanceof LazyRoute ? $route->resolve() : $route;

        return $resolved;
    }
}
