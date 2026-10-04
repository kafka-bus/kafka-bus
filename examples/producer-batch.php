<?php


use KafkaBus\Core\BusInterface;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
require 'bus.php';

$time = microtime(true);
$messages = [];

foreach (range(1, 50) as $i) {
    $messages[] = new ProducerMessageFaker("$time-test-message-$i");
}

$bus->publishBatch(MessageBatch::fromArray($messages));
