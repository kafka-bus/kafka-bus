<?php

use KafkaBus\Core\Bus;

require '../vendor/autoload.php';

/** @var Bus $bus */
require 'bus.php';

$routes = $bus->publisher()->routes();

foreach ($routes as $route) {
    echo "{$route->messageClass} => {$route->topic->name}\n";
}
