<?php

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<mixed, MessagePipelineHandler>
 */
interface MessagePipelineMiddleware extends PipelineMiddlewareInterface
{
}
