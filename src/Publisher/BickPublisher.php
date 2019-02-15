<?php

namespace Bick\Publisher;

use Bick\Connection\BickConnection;
use Bick\Connection\BickConnectionShutdownInterface;
use Bick\Consumer\BickConsumerInterface;
use Bick\Exception\BickException;
use Bick\Message\BickMessageInterface;
use Bick\Storage\MessagePersistenceInterface;
use Bick\Storage\PersistenceAdapterInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;

/**
 * Class BickPublisher
 * @package Bick\Service\Publisher
 */
final class BickPublisher implements
    BickConnectionShutdownInterface,
    BickPublisherInterface,
    MessagePersistenceInterface
{

    /**
     * Contains a persistence adapter
     *
     * @var PersistenceAdapterInterface
     */
    private $adapter;

    /**
     * @var bool
     */
    private $persistMessages = true;

    /**
     * @var BickConnection
     */
    private $connection;

    /**
     * @var array
     */
    private $publishingOptions;

    /**
     * BickPublisher constructor.
     *
     * @param BickConnection $connection
     */
    public function __construct(BickConnection $connection)
    {
        $this->connection = $connection;

        register_shutdown_function([$this, 'close']);
    }

    /**
     * @param bool $persist
     * @return $this
     */
    public function persistMessages(bool $persist)
    {
        $this->persistMessages = $persist;

        return $this;
    }

    /**
     * [
     *  'callback_ack' => callable
     *      (will receive an AMQPMessage object)
     *
     *  'callback_nack' => callable
     *      (will receive an AMQPMessage object)
     *
     *  'callback_return' => callable
     *      (will receive a reply code, text, exchange, routingKey and an AMQPMessage object)
     *      (can only be combined with callback_ack)
     * ]
     *
     * @param array $options
     */
    public function setPublishingOptions(array $options)
    {
        if (isset($options['callback_ack'])) {
            $this->connection->getChannel()->set_ack_handler($options['callback_ack']);
        }

        if (isset($options['callback_return'])) {
            $this->connection->getChannel()->set_return_listener($options['callback_return']);
            $this->publishingOptions['mandatory'] = true;
        }

        if (isset($options['callback_nack'])) {
            $this->connection->getChannel()->set_nack_handler($options['callback_nack']);
            //If callback nack, return is not needed anymore
            $this->publishingOptions['callback_return'] = null;
            $this->publishingOptions['mandatory'] = false;
        }

        if (isset($options['callback_ack']) ||
            isset($options['callback_nack']) ||
            isset($options['callback_return'])) {
            $this->connection->getChannel()->confirm_select();
            $this->publishingOptions['confirm'] = true;
        }
    }

    /**
     * @param BickMessageInterface $message
     *
     * @return void
     *
     * @throws BickException
     */
    public function publish(BickMessageInterface $message): void
    {
        if (!$message->isValid()) {
            throw new BickException('The provided BickMessageInterface is invalid');
        }

        if (true === $this->persistMessages) {
            try {
                $message->setUuid(Uuid::uuid4()->toString());
                $message->setBatchUuid(Uuid::uuid4()->toString());
            } catch (\Exception $exception) {
                throw new BickException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        $payload = new AMQPMessage(
            $message->getRaw(),
            $message->getProperties()
        );

        $this->connection->getChannel()
            ->basic_publish(
                $payload,
                $message->getExchange(),
                $message->getRoutingKey(),
                isset($this->publishingOptions['mandatory']) ? $this->publishingOptions['mandatory'] : false,
                isset($this->publishingOptions['immediate']) ? $this->publishingOptions['immediate'] : false,
                isset($this->publishingOptions['ticket']) ? $this->publishingOptions['ticket'] : false
            );

        if (isset($this->publishingOptions['confirm'])) {
            if (isset($this->publishingOptions['mandatory'])) {
                $this->connection->getChannel()->wait_for_pending_acks_returns();
            } else {
                $this->connection->getChannel()->wait_for_pending_acks();
            }
        }

        $this->persist($message);
    }

    /**
     * @param BickMessageInterface[] $messages
     * @param array $options
     *
     * @return void
     * @throws BickException
     */
    public function publishBatch(array $messages): void
    {
        try {
            $batchId = Uuid::uuid4()->toString();
        } catch (\Exception $exception) {
            throw new BickException($exception->getMessage(), $exception->getCode(), $exception);
        }

        foreach ($messages as $message) {
            if (!$message->isValid()) {
                throw new BickException('The provided BickMessageInterface is invalid');
            }

            if (true === $this->persistMessages) {
                try {
                    $message->setUuid(Uuid::uuid4()->toString());
                } catch (\Exception $exception) {
                    throw new BickException($exception->getMessage(), $exception->getCode(), $exception);
                }
                $message->setBatchUuid($batchId);
            }

            $payload = new AMQPMessage(
                $message->getRaw(),
                $message->getProperties()
            );

            $this->connection
                ->getChannel()
                ->batch_basic_publish(
                    $payload,
                    $message->getExchange(),
                    $message->getRoutingKey(),
                    isset($this->publishingOptions['mandatory']) ? $this->publishingOptions['mandatory'] : false,
                    isset($this->publishingOptions['immediate']) ? $this->publishingOptions['immediate'] : false,
                    isset($this->publishingOptions['ticket']) ? $this->publishingOptions['ticket'] : false
                );

            $this->persist($message);
        }

        $this->connection->getChannel()->publish_batch();

        if (isset($this->publishingOptions['confirm']) && $this->publishingOptions['confirm'] === true) {
            $this->connection->getChannel()->wait_for_pending_acks_returns();
        }
    }


    /**
     * @param BickMessageInterface $message
     */
    public function persist(BickMessageInterface $message): void
    {
        if (false === $this->persistMessages) {
            return;
        }

        if (!is_object($this->getPersistenceAdapter())) {
            return;
        }

        $this->getPersistenceAdapter()->persist($message);
    }

    /**
     * @param PersistenceAdapterInterface $adapter
     *
     * @return BickPublisherInterface
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $adapter): BickPublisherInterface
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return PersistenceAdapterInterface
     */
    public function getPersistenceAdapter(): PersistenceAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        $this->connection->close();
    }
}


