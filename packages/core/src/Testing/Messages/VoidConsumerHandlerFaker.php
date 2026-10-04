<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

final class VoidConsumerHandlerFaker
{
    public function __invoke(ConsumerMessageInterface $message): void
    {

    }
}
