<?php

namespace KafkaBus\Core\Consumers\Attributes;

use Attribute;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class MessageFactory
{
    public function __construct(
        public MessageFactoryInterface $messageFactory,
    ) {
    }
}
