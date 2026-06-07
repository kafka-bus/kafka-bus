<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Topics;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Connections\Kafka\KafkaConsumerFactory;
use KafkaBus\Core\Connections\Topics\Consumers\ConsumerPartition;
use KafkaBus\Core\Connections\Topics\Consumers\PartitionOffset;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Interfaces\Connections\Topics\ConnectionConsumerTopicsInterface;
use KafkaBus\Core\Interfaces\Connections\Topics\ConnectionTopicsInterface;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\TopicPartition;

final class ConsumerTopics implements ConnectionConsumerTopicsInterface
{
    protected KafkaConsumerFactory $consumerFactory;

    protected ?KafkaConsumer $consumer = null;

    public function __construct(
        protected string                    $connectionName,
        protected Options                   $config,
        protected ConsumerConfig            $consumerConfig,
        protected ConnectionTopicsInterface $topics
    ) {
        $this->consumerFactory = new KafkaConsumerFactory($this->config);
    }

    public function consumer(): KafkaConsumer
    {
        if ($this->consumer === null) {
            $this->consumer = $this->consumerFactory
                ->make($this->consumerConfig);
        }

        return $this->consumer;
    }

    public function getConsumerPartitions(string $topicName): array
    {
        $connectionTopic = $this->topics->get($topicName);

        $topicPartitions = array_map(
            fn (ConnectionPartition $partition) => new TopicPartition($topicName, $partition->id),
            $connectionTopic->partitions
        );

        /** @var list<TopicPartition> $commitedPartitions */
        $commitedPartitions = $this->consumer()
            ->getCommittedOffsets($topicPartitions, 10_000);

        return array_map(
            fn (TopicPartition $topicPartition) => $this->makePartitionCommited($topicPartition, $topicName),
            $commitedPartitions,
        );
    }

    private function makePartitionCommited(TopicPartition $topicPartition, string $topicName): ConsumerPartition
    {
        [$earlyOffset, $latestOffset] = $this->getMinAndMaxOffset($topicPartition->getTopic(), $topicPartition->getPartition());

        return new ConsumerPartition(
            $topicPartition->getPartition(),
            $topicName,
            $this->connectionName,
            $topicPartition->getOffset(),
            $earlyOffset,
            $latestOffset,
        );
    }

    /**
     * @param string $topicName
     * @param int $partition
     * @return array{0: int, 1: int}
     */
    private function getMinAndMaxOffset(string $topicName, int $partition): array
    {
        $this->consumer()
            ->queryWatermarkOffsets($topicName, $partition, $earlyOffset, $latestOffset, 1000);

        return [$earlyOffset, $latestOffset]; // @phpstan-ignore-line
    }

    /**
     * @param list<PartitionOffset> $offsets
     * @return void
     *
     * @throws Exception
     */
    public function setOffset(array $offsets): void
    {
        $topicPartitions = array_map(fn (PartitionOffset $offset) => new TopicPartition(
            $offset->topicName,
            $offset->partition,
            $offset->offset,
        ), $offsets);

        $this->consumer()
            ->commit($topicPartitions);
    }
}
