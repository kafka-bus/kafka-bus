<?php

namespace KafkaBus\Core\Bus\Publishers\Router;

use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Topics\TopicRegistry;

class PublisherRoutesBuilder
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

    public function build(): PublisherRoutes
    {
        return array_reduce(
            $this->routes,
            static fn (PublisherRoutes $routes, Route $route) => $routes->add($route),
            new PublisherRoutes()
        );
    }
}
