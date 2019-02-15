<?php

namespace Bick\Connection;

/**
 * Interface BickConnectionShutdownInterface
 * @package Bick\Interfaces
 */
interface BickConnectionShutdownInterface
{
    /**
     * Close the current bick connection
     * by executing the close method
     * on the BickConnection
     *
     * @return void
     */
    public function close(): void;
}
