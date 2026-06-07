<?php

namespace KafkaBus\Core\Exceptions;

use LogicException;

class TopicCannotResolvedException extends LogicException
{
    public static function topicNotFound(string $topicName): self
    {
        return new self("Topic [$topicName] not found");
    }
}
