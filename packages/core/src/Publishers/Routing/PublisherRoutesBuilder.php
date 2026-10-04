<?php

namespace KafkaBus\Core\Publishers\Routing;

use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Routing\RouteCollection;
use KafkaBus\Core\Topics\TopicRegistry;

final class PublisherRoutesBuilder
{
    /**
     * @var Route[]
     */
    private array $routes = [];

    private function __construct(
        private readonly TopicRegistry $topicRegistry,
    ) {
    }

    public static function make(TopicRegistry $topicRegistry): self
    {
        return new self($topicRegistry);
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param class-string<TMessage> $messageClass
     * @param string $topicKey
     * @param Options $options
     * @return $this
     */
    public function add(string $messageClass, string $topicKey, Options $options = new Options()): self
    {
        $this->routes[] = new Route($messageClass, $this->topicRegistry->get($topicKey), $options);
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
