<?php

namespace KafkaBus\Core\Bus\Publishers\Router;

use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

class PublisherRoutes
{
    /**
     * @var array<class-string, Route>
     */
    protected array $routes = [];

    public function add(Route $route): self
    {
        $this->routes[$route->messageClass] = $route;

        return $this;
    }

    /**
     * @return list<Route>
     */
    public function all(): array
    {
        return array_values($this->routes);
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param class-string<TMessage> $messageClass
     * @return Route<TMessage>|null
     */
    public function get(string $messageClass): ?Route
    {
        return $this->routes[$messageClass] ?? null;
    }
}
