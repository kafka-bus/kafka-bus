<?php

declare(strict_types=1);

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;

/**
 * @internal
 */
final readonly class RouteHandler
{
    public function __construct(
        private MessageHandlerInterface $messageHandler,
    ) {
    }

    public function __invoke(ConsumerMessageInterface $rawMessage): void
    {
        $this->messageHandler
            ->handle($rawMessage);
    }
}
