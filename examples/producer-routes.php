<?php

use KafkaBus\Core\Interfaces\Bus\BusInterface;

require '../vendor/autoload.php';

/** @var BusInterface $bus */
require 'bus.php';

$routes = $bus->routes();

foreach ($routes as $route) {
    echo "{$route->messageClass} => {$route->topic->name}\n";
}
