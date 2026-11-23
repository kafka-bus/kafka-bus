<?php

declare(strict_types=1);

namespace KafkaBus\Core\Consumers;

use Psr\Log\LoggerInterface;

final readonly class ConsumerHandlerFactory implements ConsumerHandlerFactoryInterface
{
    public function __construct(
        private LoggerInterface|null $logger = null,
    ) {
    }

    public function create(): ConsumerHandlerInterface
    {
        return $this->logger
            ? new LoggerConsumerHandler($this->logger, new ConsumerHandler())
            : new ConsumerHandler();
    }
}
