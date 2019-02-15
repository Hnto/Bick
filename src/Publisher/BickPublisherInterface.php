<?php

namespace Bick\Publisher;

use Bick\Connection\BickConnection;
use Bick\Message\BickMessage;
use Bick\Message\BickMessageInterface;

/**
 * Interface BickPublisherInterface
 * @package Bick\Interfaces
 */
interface BickPublisherInterface
{
    /**
     * The construct method must receive
     * a bick connection object in order
     * to publish messages
     *
     * @param BickConnection $connection
     */
    public function __construct(BickConnection $connection);

    /**
     * The publish method must receive
     * a bick message object
     *
     * @param BickMessageInterface $message
     * @return void
     */
    public function publish(BickMessageInterface $message): void;

    /**
     * The publishBatch method
     * must receive an array
     * of bick message objects
     *
     * @param BickMessage[] $messages
     * @return void
     */
    public function publishBatch(array $messages): void;
}