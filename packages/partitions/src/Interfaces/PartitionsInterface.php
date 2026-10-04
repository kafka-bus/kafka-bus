<?php

declare(strict_types=1);

namespace KafkaBus\Partitions\Interfaces;

use KafkaBus\Partitions\CommitOffset;
use KafkaBus\Partitions\CommitOffsetResult;
use KafkaBus\Partitions\Exceptions\CannotCommitOffsetException;
use KafkaBus\Partitions\TopicPartition;

interface PartitionsInterface
{
    /**
     * @return iterable<TopicPartition>
     */
    public function list(): iterable;

    /**
     * @param CommitOffset $commitOffset
     * @return list<CommitOffsetResult>
     *
     * @throws CannotCommitOffsetException
     */
    public function setOffset(CommitOffset $commitOffset): array;
}
