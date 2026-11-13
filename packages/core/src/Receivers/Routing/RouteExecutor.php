<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineHandler;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;

final readonly class RouteExecutor
{
    /**
     * @param HandlerExecutor $executor
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public function __construct(
        private HandlerExecutor $executor,
        private array $middleware = []
    ) {
    }

    public function execute(ConsumerMessageInterface $message): void
    {
        $pipelineHandler = new ReceiverPipelineHandler($message, $this->executor);

        $pipeline = PipelineBuilder::for($pipelineHandler)
            ->middleware($this->middleware)
            ->create();

        $pipeline->start();
    }
}
