<?php

namespace KafkaBus\Core\Interfaces\Producers\Messages;

interface HasHeaders
{
    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;
}
