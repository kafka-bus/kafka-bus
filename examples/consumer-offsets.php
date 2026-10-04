<?php

use KafkaBus\Core\Connections\Registry\ConnectionRegistryInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Metadata\Metadata;
use KafkaBus\Metadata\Partitions\CommitOffset;
use KafkaBus\Metadata\Partitions\Offset;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$topic = $topicRegistry->get('products');

$partitions = Metadata::fromConnection($connectionRegistry->connection('default'))
    ->partitions([$topic], new ConsumerConfig(additionalOptions: ['group.id' => 'products-microservice']));

$results = $partitions->setOffset(new CommitOffset($topic, 0, Offset::Early));

foreach ($results as $result) {
    echo "{$result->topic->name}#$result->partition O:$result->oldOffset N:$result->newOffset\n";
}
