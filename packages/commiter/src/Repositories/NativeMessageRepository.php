<?php

namespace KafkaBus\Commiter\Repositories;

use DateTimeImmutable;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Commiter\Attempt;
use KafkaBus\Commiter\Interfaces\ConsumerMessageRepositoryInterface;
use KafkaBus\Commiter\Interfaces\RepositorySourceInterface;

final readonly class NativeMessageRepository implements ConsumerMessageRepositoryInterface
{
    public function __construct(
        private RepositorySourceInterface $source,
    ) {
    }

    public function attempt(ConsumerMessageInterface $message): Attempt
    {
        return $this->source->get($message->msgId())
            ?? new Attempt($message->msgId(), 1, new DateTimeImmutable());
    }

    public function failed(ConsumerMessageInterface $message): void
    {
        $this->source->increment($message->msgId());
    }

    public function commit(ConsumerMessageInterface $message): void
    {
        $this->source->commit($message->msgId());
    }
}
