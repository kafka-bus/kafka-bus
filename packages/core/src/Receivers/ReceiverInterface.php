<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;

interface ReceiverInterface
{
    /**
     * @throws MessageConsumerNotHandledException
     */
    public function dispatch(ConsumerMessageInterface $message): void;
}
