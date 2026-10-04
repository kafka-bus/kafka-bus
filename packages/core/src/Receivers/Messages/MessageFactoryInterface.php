<?php

namespace KafkaBus\Core\Receivers\Messages;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

interface MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): mixed;
}
