<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Routing\RouteCollection;
use KafkaBus\Core\Topics\TopicRegistry;

final class ReceiverRoutesBuilder
{
    /**
     * @var Route[]
     */
    private array $routes = [];

    private function __construct(
        private readonly TopicRegistry $topicRegistry,
    ) {
    }

    public static function make(TopicRegistry $topicRegistry): ReceiverRoutesBuilder
    {
        return new self($topicRegistry);
    }

    public function add(RouteInfo $routeInfo): self
    {
        $this->routes[] = new Route(
            $this->topicRegistry->get($routeInfo->topicKey),
            HandlerExecutor::fromHandler($routeInfo->handler),
            $routeInfo->middleware
        );

        return $this;
    }

    /**
     * @return RouteCollection<Route>
     */
    public function build(): RouteCollection
    {
        return array_reduce(
            $this->routes,
            static fn (RouteCollection $routes, Route $route) => $routes->add($route),
            new RouteCollection(Route::class)
        );
    }
}
