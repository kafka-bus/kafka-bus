<?php

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Consumers\Messages\NativeMessageFactory;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Topics\TopicRegistry;

final class ConsumerRoutesBuilder
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

    public static function make(TopicRegistry $topicRegistry, ?MessageFactoryInterface $messageFactory = null): ConsumerRoutesBuilder
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

    public function build(): ConsumerRoutes
    {
        return array_reduce(
            $this->routes,
            static fn (ConsumerRoutes $routes, Route $route) => $routes->add($route),
            new ConsumerRoutes()
        );
    }
}
