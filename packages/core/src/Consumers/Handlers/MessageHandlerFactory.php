<?php

namespace KafkaBus\Core\Consumers\Handlers;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Consumers\Router\ConsumerRouter;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerFactoryInterface;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;

final class MessageHandlerFactory implements MessageHandlerFactoryInterface
{
    public function create(Worker $worker): MessageHandlerInterface
    {
        return new MessageHandler(new ConsumerRouter($worker->routes));
    }
}
