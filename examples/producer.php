<?php


use KafkaBus\Core\Interfaces\Bus\BusInterface;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
require 'bus.php';

$bus->publish(new ProducerMessageFaker('test-message', ['foo' => 'bar']));
