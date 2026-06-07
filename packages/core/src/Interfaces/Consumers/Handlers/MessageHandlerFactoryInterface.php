<?php

namespace KafkaBus\Core\Interfaces\Consumers\Handlers;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;

interface MessageHandlerFactoryInterface
{
    public function create(Worker $worker): MessageHandlerInterface;
}
