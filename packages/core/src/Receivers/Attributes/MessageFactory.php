<?php

namespace KafkaBus\Core\Receivers\Attributes;

use Attribute;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class MessageFactory
{
    public function __construct(
        public MessageFactoryInterface $messageFactory,
    ) {
    }
}
