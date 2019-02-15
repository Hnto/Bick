<?php

namespace Bick\Storage;

use Bick\Message\BickMessageFaultInterface;
use Bick\Message\BickMessageInterface;

/**
 * Interface PersistenceAdapterInterface
 * @package Bick\Interfaces
 */
interface PersistenceAdapterInterface
{
    /**
     * @param BickMessageInterface $message
     */
    public function persist(BickMessageInterface $message): void;

    /**
     * @param BickMessageInterface $message
     */
    public function update(BickMessageInterface $message): void;

    /**
     * @param BickMessageInterface $message
     * @param BickMessageFaultInterface $fault
     */
    public function analyse(BickMessageInterface $message, BickMessageFaultInterface $fault): void;
}
