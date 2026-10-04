<?php

declare(strict_types=1);

namespace KafkaBus\Worker;

use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Worker\Consumers\ConsumerStreamInterface;

final readonly class WorkerRunner
{
    /**
     * @param ConsumerStreamInterface $stream
     * @param list<Topic> $topics
     */
    public function __construct(
        private ConsumerStreamInterface $stream,
        private array $topics,
    ) {
    }

    /**
     * @throws MessageConsumerException
     * @throws MessageConsumerNotHandledException
     * @throws ConsumerException
     */
    public function run(): void
    {
        $this->stream
            ->listen($this->topics);
    }

    public function forceStop(): void
    {
        $this->stream
            ->forceStop();
    }
}
