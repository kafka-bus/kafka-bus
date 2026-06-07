<?php


use KafkaBus\Core\Interfaces\Bus\BusInterface;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
require 'bus.php';

pcntl_async_signals(true);

$listener = $bus->listener('default-listener');

pcntl_signal(SIGINT, fn () => $listener->forceStop());

$listener->listen();

