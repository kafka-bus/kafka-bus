<?php

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Consumers\Pipelines\MessagePipelineHandler;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;

final readonly class RouteExecutor
{
    /**
     * @param callable $handler
     * @param MessageFactoryInterface $factory
     * @param list<MessagePipelineMiddleware> $middleware
     */
    public function __construct(
        protected mixed $handler,
        protected MessageFactoryInterface $factory,
        protected array $middleware = []
    ) {
    }

    public function execute(ConsumerMessageInterface $message): void
    {
        $pipelineHandler = new MessagePipelineHandler($this->handler, $this->map($message));

        $pipeline = PipelineBuilder::for($pipelineHandler)
            ->middleware($this->middleware)
            ->create();

        $pipeline->start();
    }

    private function map(ConsumerMessageInterface $message): mixed
    {
        return $this->factory
            ->fromKafka($message);
    }
}
