<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

final readonly class UnroutableMessage
{
    public function __construct(
        public ConsumerMessageInterface $message,
        public UnroutableReason $reason,
        public ?string $messageType = null,
    ) {
    }
}
