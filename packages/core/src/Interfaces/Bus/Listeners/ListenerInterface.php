<?php

namespace KafkaBus\Core\Interfaces\Bus\Listeners;

interface ListenerInterface
{
    public function partitions(): PartitionsInterface;

    public function forceStop(): void;

    public function listen(): void;
}
