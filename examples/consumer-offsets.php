<?php

use KafkaBus\Core\Bus\Listeners\Partitions\CommitOffset;
use KafkaBus\Core\Bus\Listeners\Partitions\Offset;
use KafkaBus\Core\Interfaces\Bus\BusInterface;
use KafkaBus\Core\Topics\TopicRegistry;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
/** @var TopicRegistry $topicRegistry */
require 'bus.php';

$commitOffset = new CommitOffset($topicRegistry->get('products'), 0, Offset::Early);

$partitions = $bus->listener('default-listener')
    ->partitions()
    ->setOffset($commitOffset);

foreach ($partitions as $partition) {
    echo "{$partition->topic->name}#$partition->partition O:$partition->oldOffset N:$partition->newOffset\n";
}
