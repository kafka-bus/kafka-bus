<?php

namespace KafkaBus\Core\Bus\Publishers;

use KafkaBus\Core\Bus\MessageBatch;
use KafkaBus\Core\Bus\Publishers\Router\PublisherRouter;
use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

class Publisher
{
    public function __construct(
        protected PublisherRouter $router
    ) {
    }

    /**
     * @return list<Route>
     */
    public function routes(): array
    {
        return $this->router
            ->routes();
    }

    /**
     * @template TMessage of ProducerMessageInterface
     *
     * @param MessageBatch<TMessage> $messageBatch
     * @return void
     */
    public function publish(MessageBatch $messageBatch): void
    {
        $this->router
            ->publish($messageBatch);
    }
}
