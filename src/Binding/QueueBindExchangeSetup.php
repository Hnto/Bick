<?php

namespace Bick\Binding;

use Bick\Traits\ObjectAsArray;
use Bick\Traits\ObjectAsArrayInterface;

final class QueueBindExchangeSetup implements ObjectAsArrayInterface
{
    use ObjectAsArray;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * QueueBindExchangeSetup constructor.
     *
     * @param string $queue
     * @param string $exchange
     * @param string $routingKey
     */
    public function __construct(string $queue, string $exchange, string $routingKey)
    {
        $this->queue = $queue;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }
}
