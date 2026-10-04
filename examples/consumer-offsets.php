<?php

use KafkaBus\Core\Connections\KafkaConnection;
use KafkaBus\Core\Connections\Registry\ConnectionRegistryInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Partitions\CommitOffset;
use KafkaBus\Partitions\Offset;
use KafkaBus\Partitions\Partitions;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$topic = $topicRegistry->get('products');

/** @var KafkaConnection $connection */
$connection = $connectionRegistry->connection('default');

$consumerTopics = $connection->topics()
    ->consume(new ConsumerConfig(additionalOptions: ['group.id' => 'products-microservice']));

$partitions = new Partitions([$topic], 'products-microservice', $consumerTopics);

$results = $partitions->setOffset(new CommitOffset($topic, 0, Offset::Early));

foreach ($results as $result) {
    echo "{$result->topic->name}#$result->partition O:$result->oldOffset N:$result->newOffset\n";
}
