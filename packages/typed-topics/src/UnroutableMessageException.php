<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use RuntimeException;

final class UnroutableMessageException extends RuntimeException
{
    public function __construct(public readonly UnroutableMessage $unroutable)
    {
        parent::__construct(\sprintf(
            'Cannot route Kafka message [%s]: %s.',
            $unroutable->message->msgId(),
            $unroutable->reason->value,
        ));
    }
}
