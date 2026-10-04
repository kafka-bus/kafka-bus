<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\NativeMessageFactory;
use KafkaBus\Core\Routing\RouteCollection;
use KafkaBus\Core\Topics\TopicRegistry;

final class ReceiverRoutesBuilder
{
    /**
     * @var Route[]
     */
    private array $routes = [];

    private MessageFactoryExtractor $extractor;

    private function __construct(
        private readonly TopicRegistry $topicRegistry,
        private readonly MessageFactoryInterface $defaultMessageFactory,
    ) {
        $this->extractor = new MessageFactoryExtractor();
    }

    public static function make(TopicRegistry $topicRegistry, ?MessageFactoryInterface $messageFactory = null): ReceiverRoutesBuilder
    {
        return new self($topicRegistry, $messageFactory ?? new NativeMessageFactory());
    }

    public function add(RouteInfo $routeInfo): self
    {
        $messageFactory = $this->extractor->extract($routeInfo->handler)
            ?? $this->defaultMessageFactory;

        $this->routes[] = new Route(
            $this->topicRegistry->get($routeInfo->topicKey),
            $routeInfo->handler,
            $messageFactory,
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
