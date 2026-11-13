<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;

final readonly class HeaderMessageFactory implements MessageFactoryInterface
{
    /**
     * @internal Created by MessageRoutes.
     *
     * @param array<string, MessageRoute> $routes
     */
    public function __construct(
        private array $routes,
        private ?MessageRoute $whenHeaderMissing = null,
    ) {
    }

    public function fromKafka(ConsumerMessageInterface $message): mixed
    {
        $headers = $message->headers();
        $header = HasMessageType::HEADER_NAME;

        $type = $headers[$header] ?? null;

        if (\is_null($type)) {
            return $this->whenHeaderMissing?->decode($message)
                ?? new UnroutableMessage($message, UnroutableReason::MissingHeader);
        }

        return ($this->routes[$type] ?? null)?->decode($message)
            ?? new UnroutableMessage($message, UnroutableReason::UnknownType);
    }
}
