<?php

namespace KafkaBus\Workbench\Data;

enum CategoryStatusEnum: string
{
    case Active = 'active';
    case Archived = 'archived';
}