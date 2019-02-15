<?php

namespace Bick\Storage;

use Bick\Message\BickMessageInterface;

/**
 * Interface MessagePersistenceInterface
 * @package Bick\Interfaces
 */
interface MessagePersistenceInterface
{
    /**
     * @param BickMessageInterface $message
     */
    public function persist(BickMessageInterface $message): void;
}
