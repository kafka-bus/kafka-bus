<?php

namespace KafkaBus\Core\Interfaces\Bus\Listeners;

use KafkaBus\Core\Bus\Listeners\Partitions\CommitOffset;
use KafkaBus\Core\Bus\Listeners\Partitions\CommitOffsetResult;
use KafkaBus\Core\Bus\Listeners\Partitions\TopicPartition;
use KafkaBus\Core\Exceptions\Listeners\CannotCommitOffsetException;

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
