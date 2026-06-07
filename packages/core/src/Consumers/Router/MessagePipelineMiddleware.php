<?php

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Consumers\Pipelines\MessagePipelineHandler;
use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<MessagePipelineHandler>
 */
interface MessagePipelineMiddleware extends PipelineMiddlewareInterface
{
}
