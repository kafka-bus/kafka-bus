<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus\Listeners\Partitions;

enum Offset
{
    case Early;
    case Latest;
}
