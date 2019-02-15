<?php

namespace Bick\Connection;

use Bick\Traits\ClassConstructorProps;
use Bick\Traits\ObjectAsArray;
use Bick\Traits\ObjectAsArrayInterface;

/**
 * Class BickConnectionSetup
 * @package Bick\Connection
 */
final class BickConnectionSetup implements ObjectAsArrayInterface
{
    use ObjectAsArray, ClassConstructorProps;

    /**
     * @var mixed
     */
    private $host;

    /**
     * @var mixed
     */
    private $port;

    /**
     * @var mixed
     */
    private $user;

    /**
     * @var mixed
     */
    private $pass;

    /**
     * @var mixed
     */
    private $vhost;

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @return mixed
     */
    public function getVhost()
    {
        return $this->vhost;
    }
}
