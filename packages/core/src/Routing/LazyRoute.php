<?php

declare(strict_types=1);

namespace KafkaBus\Core\Routing;

use Closure;
use LogicException;

/**
 * @template TRoute of RouteInterface
 */
final class LazyRoute implements RouteInterface
{
    /**
     * @var TRoute|null
     */
    private ?RouteInterface $route = null;

    /**
     * @param Closure(): TRoute $factory
     */
    public function __construct(
        private readonly string $key,
        private readonly Closure $factory,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    /**
     * @return TRoute
     */
    public function resolve(): RouteInterface
    {
        if ($this->route !== null) {
            return $this->route;
        }

        $route = ($this->factory)();

        if ($route->key() !== $this->key) {
            throw new LogicException(\sprintf(
                'LazyRoute was registered under key [%s], but its factory built a route keyed [%s].',
                $this->key,
                $route->key(),
            ));
        }

        return $this->route = $route;
    }
}
