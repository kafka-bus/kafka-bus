<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Bus\Listeners\Workers\Options;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamFactoryInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamInterface;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerFactoryInterface;

class ConsumerStreamFactory implements ConsumerStreamFactoryInterface
{
    public function __construct(
        protected MessageHandlerFactoryInterface $messageHandlerFactory = new MessageHandlerFactory(),
    ) {
    }

    public function create(ConnectionInterface $connection, Worker $worker): ConsumerStreamInterface
    {
        $configuration = $this->makeConsumerConfig($worker->options);

        $messageHandler = $this->messageHandlerFactory
            ->create($worker);

        return new ConsumerStream(
            $connection->createConsumer($messageHandler->topics(), $configuration),
            $messageHandler,
        );
    }

    private function makeConsumerConfig(Options $options): ConsumerConfig
    {
        return new ConsumerConfig(
            additionalOptions: $options->additionalOptions,
            autoCommit: $options->autoCommit,
            consumerTimeout: $options->consumerTimeout,
        );
    }
}
