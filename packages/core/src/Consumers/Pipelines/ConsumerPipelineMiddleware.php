<?php

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<ConsumerMessageInterface, ConsumerPipelineHandler>
 */
interface ConsumerPipelineMiddleware extends PipelineMiddlewareInterface
{
}
