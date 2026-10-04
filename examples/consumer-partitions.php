<?php

use KafkaBus\Core\Connections\Registry\ConnectionRegistryInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Metadata\Metadata;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$partitions = Metadata::fromConnection($connectionRegistry->connection('default'))
    ->partitions($topicRegistry->all(), new ConsumerConfig(additionalOptions: ['group.id' => 'products-microservice']));

foreach ($partitions->list() as $partition) {
    echo "{$partition->topic->name}#$partition->id C:$partition->currentOffset MIN:$partition->minOffset MAX:$partition->maxOffset\n";
}
