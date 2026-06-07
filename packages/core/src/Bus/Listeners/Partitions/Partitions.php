<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus\Listeners\Partitions;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Connections\Topics\Consumers\ConsumerPartition;
use KafkaBus\Core\Connections\Topics\Consumers\PartitionOffset;
use KafkaBus\Core\Exceptions\Listeners\CannotCommitOffsetException;
use KafkaBus\Core\Exceptions\TopicNotFoundException;
use KafkaBus\Core\Interfaces\Bus\Listeners\PartitionsInterface;
use KafkaBus\Core\Interfaces\Connections\Topics\ConnectionConsumerTopicsInterface;
use KafkaBus\Core\Topics\Topic;

final class Partitions implements PartitionsInterface
{
    /**
     * @param Worker $worker
     * @param ConnectionConsumerTopicsInterface $consumerTopics
     */
    public function __construct(
        protected Worker                            $worker,
        protected ConnectionConsumerTopicsInterface $consumerTopics,
    ) {
    }

    public function list(): iterable
    {
        foreach ($this->worker->topics() as $topic) {
            $partitions = $this->getTopicPartitions($topic);

            foreach ($partitions as $partition) {
                yield $partition;
            }
        }
    }

    /**
     * @param Topic $topic
     * @return iterable<TopicPartition>
     */
    private function getTopicPartitions(Topic $topic): iterable
    {
        try {
            $partitions = $this->consumerTopics
                ->getConsumerPartitions($topic->name);

            foreach ($partitions as $partition) {
                yield new TopicPartition(
                    id: $partition->id,
                    topic: $topic,
                    currentOffset: $partition->currentOffset,
                    minOffset: $partition->minOffset,
                    maxOffset: $partition->maxOffset,
                );
            }
        }
        catch (TopicNotFoundException) {
            yield new TopicPartition(id: -1, topic: $topic);
        }
    }

    public function setOffset(CommitOffset $commitOffset): array
    {
        try {
            $this->throwIfTopicNotFound($commitOffset->topic);

            $partitions = $this->getPartitionsGroupedById($commitOffset->topic);

            $partitionOffsets = $commitOffset->partition == RD_KAFKA_PARTITION_UA
                ? $this->commitMany($commitOffset, $partitions)
                : $this->commitOne($commitOffset, $partitions);

            $this->consumerTopics
                ->setOffset($partitionOffsets);

            return array_map(function (PartitionOffset $partitionOffset) use ($commitOffset, $partitions) {
                return new CommitOffsetResult(
                    topic: $commitOffset->topic,
                    partition: $partitionOffset->partition,
                    oldOffset: $partitions[$partitionOffset->partition]->currentOffset,
                    newOffset: $partitionOffset->offset,
                );
            }, $partitionOffsets);
        }
        catch (TopicNotFoundException $exception) {
            throw new CannotCommitOffsetException(
                "Topic '{$commitOffset->topic->name}' not found for worker '{$this->worker->name}'",
                previous: $exception
            );
        }
    }

    /**
     * @param Topic $topic
     * @return void
     *
     * @throws CannotCommitOffsetException
     */
    private function throwIfTopicNotFound(Topic $topic): void
    {
        $topicKeys = array_map(fn (Topic $topic) => $topic->key, $this->worker->topics());

        if (!\in_array($topic->key, $topicKeys)) {
            throw new CannotCommitOffsetException(
                "Topic '{$topic->key}' not found for worker '{$this->worker->name}'"
            );
        }
    }

    /**
     * @param Topic $topic
     * @return array<int, ConsumerPartition>
     *
     * @throws TopicNotFoundException
     */
    private function getPartitionsGroupedById(Topic $topic): array
    {
        $partitions = $this->consumerTopics
            ->getConsumerPartitions($topic->name);

        $ids = array_map(fn (ConsumerPartition $partition) => $partition->id, $partitions);

        return array_combine($ids, $partitions);
    }

    /**
     * @param CommitOffset $commitOffset
     * @param array<int, ConsumerPartition> $partitions
     * @return list<PartitionOffset>
     *
     * @throws CannotCommitOffsetException
     */
    private function commitOne(CommitOffset $commitOffset, array $partitions): array
    {
        $partition = $partitions[$commitOffset->partition]
            ?? throw new CannotCommitOffsetException(
                "Partition '{$commitOffset->partition}' not found for topic '{$commitOffset->topic->name}'"
            );

        return [$this->convertPartitionOffset($commitOffset, $partition)];
    }

    /**
     * @param CommitOffset $commitOffset
     * @param array<int, ConsumerPartition> $partitions
     * @return list<PartitionOffset>
     *
     * @throws CannotCommitOffsetException
     */
    private function commitMany(CommitOffset $commitOffset, array $partitions): array
    {
        $partitionOffsets = array_map(
            fn (ConsumerPartition $partition) => $this->convertPartitionOffset($commitOffset, $partition),
            $partitions
        );

        return array_values($partitionOffsets);
    }

    /**
     * @param CommitOffset $commitOffset
     * @param ConsumerPartition $partition
     * @return PartitionOffset
     *
     * @throws CannotCommitOffsetException
     */
    private function convertPartitionOffset(CommitOffset $commitOffset, ConsumerPartition $partition): PartitionOffset
    {
        $offset = $commitOffset->offset;

        if ($offset instanceof Offset) {
            $offset = $offset == Offset::Latest
                ? $partition->maxOffset
                : $partition->minOffset;
        }

        if ($offset < $partition->minOffset || $offset > $partition->maxOffset) {
            throw new CannotCommitOffsetException(
                "Offset '{$offset}' is out of bounds [{$partition->minOffset}, {$partition->maxOffset}]"
            );
        }

        return new PartitionOffset(
            $commitOffset->topic->name,
            $commitOffset->partition,
            $offset,
        );
    }
}
