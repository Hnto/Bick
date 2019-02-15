<?php

namespace Bick\Connection;

use Bick\Exception\BickException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class BickConnection
 * Sets up the AMQPStreamconnection
 * and returns a channel upon
 * a valid credential login
 *
 * @package Bick
 */
final class BickConnection {

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * BickConnection constructor.
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $vhost
     *
     * @throws BickException
     */
    public function __construct($host, $port, $user, $pass, $vhost = '/')
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $host,
                $port,
                $user,
                $pass,
                empty($vhost) ? '/' : $vhost
            );
        } catch (\Exception $exception) {
            throw new BickException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->channel = $this->connection->channel();
    }

    /**
     * Returns the current AMQPChannel
     *
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * Close current bick connection
     */
    public function close()
    {
        $this->channel->close();
        $this->connection->getConnection()->close();
    }
}
