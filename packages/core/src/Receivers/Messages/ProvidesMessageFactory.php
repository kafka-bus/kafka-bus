<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Messages;

interface ProvidesMessageFactory
{
    public function messageFactory(): MessageFactoryInterface;
}
