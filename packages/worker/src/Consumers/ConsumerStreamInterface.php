<?php

namespace KafkaBus\Worker\Consumers;

use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Topics\Topic;

interface ConsumerStreamInterface
{
    /**
     * @param list<Topic> $topics
     * @return void
     *
     * @throws MessageConsumerException
     * @throws MessageConsumerNotHandledException
     * @throws ConsumerException
     */
    public function listen(array $topics): void;

    public function forceStop(): void;
}
