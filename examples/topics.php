<?php


use KafkaBus\Core\Connections\Registry\ConnectionRegistryInterface;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Metadata\Metadata;

require '../vendor/autoload.php';

/** @var ConnectionRegistryInterface $connectionRegistry */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$metadata = Metadata::fromConnection($connectionRegistry->connection('default'));

foreach ($metadata->topics()->list($topicRegistry->all()) as $topic) {
    foreach ($topic->partitions as $partition) {
        echo "$topic->name#$partition->id [$partition->offset]\n";
    }
}
