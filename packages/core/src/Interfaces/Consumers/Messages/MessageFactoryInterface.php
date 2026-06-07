<?php

namespace KafkaBus\Core\Interfaces\Consumers\Messages;

interface MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): mixed;
}
