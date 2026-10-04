<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\NativeMessageFactory;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;
use KafkaBus\Core\Routing\RouteInterface;
use KafkaBus\Core\Topics\Topic;

final readonly class Route implements RouteInterface
{
    /**
     * @param Topic $topic
     * @param callable $handler
     * @param MessageFactoryInterface $messageFactory
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public function __construct(
        public Topic $topic,
        public mixed $handler,
        public MessageFactoryInterface $messageFactory = new NativeMessageFactory(),
        public array $middleware = []
    ) {
    }

    public function key(): string
    {
        return $this->topic->name;
    }
}
