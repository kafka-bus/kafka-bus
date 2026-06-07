<?php

use KafkaBus\Core\Interfaces\Bus\BusInterface;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
require 'bus.php';

$partitions = $bus->listener('default-listener')
    ->partitions()
    ->list();

foreach ($partitions as $partition) {
    echo "{$partition->topic->name}#$partition->id C:$partition->currentOffset MIN:$partition->minOffset MAX:$partition->maxOffset\n";
}
