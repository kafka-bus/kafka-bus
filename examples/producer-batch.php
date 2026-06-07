<?php


use KafkaBus\Core\Bus\MessageBatch;
use KafkaBus\Core\Interfaces\Bus\BusInterface;
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
