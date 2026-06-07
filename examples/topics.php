<?php


use KafkaBus\Core\Connections\KafkaConnection;
use KafkaBus\Core\Interfaces\Connections\ConnectionRegistryInterface;
use KafkaBus\Core\Topics\TopicRegistry;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

/** @var KafkaConnection $connection */
$connection = $connectionRegistry->connection('default');
$topics = $connection->topics();

foreach ($topics->list() as $topic) {
    foreach ($topic->partitions as $partition) {
        echo "$topic->topicName#$partition->id [$partition->offset]\n";
    }
}
