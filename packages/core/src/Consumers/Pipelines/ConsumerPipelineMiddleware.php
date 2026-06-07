<?php

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<ConsumerPipelineHandler>
 */
interface ConsumerPipelineMiddleware extends PipelineMiddlewareInterface
{
}
