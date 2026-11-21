<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Topics\Topic;

interface ConsumerInterface
{
    /**
     * @param list<Topic> $topics
     * @return iterable<ConsumerMessageInterface>
     *
     * @throws ConsumerException
     */
    public function start(array $topics): iterable;

    public function commit(ConsumerMessageInterface $consumerMessage): void;
}
