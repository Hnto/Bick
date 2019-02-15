<?php

namespace Bick\Batch;

/**
 * Interface BickBatchInterface
 * @package Bick\Interfaces
 */
interface BickBatchInterface
{
    /**
     * Contains the status
     * for a batch that is done
     */
    public const BATCH_STATUS_DONE = 2;

    /**
     * Contains the status
     * for a batch that is busy
     */
    public const BATCH_STATUS_BUSY = 1;

    /**
     * Contains the status
     * for a batch that is in queue
     */
    public const BATCH_STATUS_IN_QUEUE = 0;
}
