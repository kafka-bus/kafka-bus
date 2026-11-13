<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

interface HasMessageType
{
    public const HEADER_NAME = 'x-message-type';

    /**
     * @return non-empty-string
     *
     */
    public function getMessageType(): string;
}
