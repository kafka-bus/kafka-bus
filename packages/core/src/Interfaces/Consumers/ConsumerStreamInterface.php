<?php

namespace KafkaBus\Core\Interfaces\Consumers;

use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;

interface ConsumerStreamInterface
{
    /**
     * @throws MessageConsumerException
     * @throws MessageConsumerNotHandledException
     * @throws ConsumerException
     */
    public function listen(): void;

    public function forceStop(): void;
}
