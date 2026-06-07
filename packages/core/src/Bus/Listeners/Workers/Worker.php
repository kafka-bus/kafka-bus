<?php

namespace KafkaBus\Core\Bus\Listeners\Workers;

use KafkaBus\Core\Consumers\Router\ConsumerRoutes;
use KafkaBus\Core\Topics\Topic;

readonly class Worker
{
    public function __construct(
        public string         $name,
        public ConsumerRoutes $routes,
        public Options        $options = new Options()
    ) {
    }

    /**
     * @return list<Topic>
     */
    public function topics(): array
    {
        return $this->routes->topics();
    }
}
