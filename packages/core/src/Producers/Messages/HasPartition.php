<?php

namespace KafkaBus\Core\Producers\Messages;

interface HasPartition
{
    public function getPartition(): int;
}
