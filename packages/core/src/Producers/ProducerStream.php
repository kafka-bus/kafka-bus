<?php

namespace KafkaBus\Core\Producers;

use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\Pipelines\ProducerPipelineHandler;

/**
 * @template TMessage of ProducerMessageInterface
 * @implements ProducerStreamInterface<TMessage>
 */
class ProducerStream implements ProducerStreamInterface
{
    /**
     * @param Route<TMessage> $route
     * @param ProducerInterface $producer
     */
    public function __construct(
        protected Route $route,
        protected ProducerInterface $producer,
    ) {
    }

    public function handle(iterable $messages): void
    {
        $this->producer
            ->produce($this->prepareMessages($messages));
    }

    /**
     * @param iterable<ProducerMessageInterface> $messages
     * @return iterable<ProducerMessage>
     */
    private function prepareMessages(iterable $messages): iterable
    {
        foreach ($messages as $message) {
            $producerHandler = new ProducerPipelineHandler($message, $this->route->topic);
            $producerMessage = PipelineBuilder::for($producerHandler)
                ->middleware($this->route->options->middleware)
                ->create()
                ->start();

            if (!\is_null($producerMessage)) {
                yield $producerMessage;
            }
        }
    }
}
