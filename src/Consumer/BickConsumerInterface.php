<?php

namespace Bick\Consumer;

use Bick\Connection\BickConnection;
use Bick\Exception\BickException;

/**
 * Interface BickConsumerInterface
 * @package Bick\Interfaces
 */
interface BickConsumerInterface
{
    /**
     * Contains the default consumer tag
     */
    public const DEFAULT_CONSUMER_TAG = 'consumer';

    /**
     * BickConsumerInterface constructor.
     * This interface requires a BickConnection
     * object containing an open connection
     *
     * @param BickConnection $connection
     */
    public function __construct(BickConnection $connection);
    
    /**
     * Consume a message from a queue
     *
     * @param string $queue
     *
     * @throws BickException
     */
    public function consume(string $queue);
}
