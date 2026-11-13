<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use KafkaBus\Core\Receivers\Messages\ProvidesMessageFactory;

abstract class TypedTopicHandler implements ProvidesMessageFactory
{
    private ?MessageRoutes $messageRoutes = null;

    abstract protected function routes(MessageRoutes $routes): void;

    final public function messageFactory(): HeaderMessageFactory
    {
        return $this->registeredRoutes()
            ->messageFactory();
    }

    /**
     * @param mixed $message
     * @param array<string, string|null> $headers
     * @return void
     */
    final public function __invoke(mixed $message, array $headers = []): void
    {
        $this->registeredRoutes()
            ->dispatch($message, $headers[HasMessageType::HEADER_NAME] ?? null);
    }

    private function registeredRoutes(): MessageRoutes
    {
        if ($this->messageRoutes === null) {
            $routes = new MessageRoutes();
            $this->routes($routes);
            $this->messageRoutes = clone $routes;
        }

        return $this->messageRoutes;
    }
}
