<?php


use KafkaBus\Core\Connections\KafkaConnection;
use KafkaBus\Core\Connections\Registry\ConnectionRegistryInterface;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
require 'bus.php';

/** @var KafkaConnection $connection */
$connection = $connectionRegistry->connection('default');

foreach ($connection->topics()->list() as $topic) {
    foreach ($topic->partitions as $partition) {
        echo "$topic->topicName#$partition->id [$partition->offset]\n";
    }
}
