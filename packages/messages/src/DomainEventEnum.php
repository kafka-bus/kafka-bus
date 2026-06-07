<?php

namespace KafkaBus\Messages;

enum DomainEventEnum: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
