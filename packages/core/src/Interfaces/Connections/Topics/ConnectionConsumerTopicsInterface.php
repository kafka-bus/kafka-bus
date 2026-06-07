<?php

namespace KafkaBus\Core\Interfaces\Connections\Topics;

use KafkaBus\Core\Connections\Topics\Consumers\ConsumerPartition;
use KafkaBus\Core\Connections\Topics\Consumers\PartitionOffset;
use KafkaBus\Core\Exceptions\TopicNotFoundException;

interface ConnectionConsumerTopicsInterface
{
    /**
     * @param string $topicName
     * @return ConsumerPartition[]
     *
     * @throws TopicNotFoundException
     */
    public function getConsumerPartitions(string $topicName): array;

    /**
     * @param PartitionOffset[] $offsets
     * @return void
     */
    public function setOffset(array $offsets): void;
}
