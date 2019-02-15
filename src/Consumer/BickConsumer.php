<?php

namespace Bick\Consumer;

use Bick\Connection\BickConnection;
use Bick\Connection\BickConnectionShutdownInterface;
use Bick\Exception\BickException;
use Bick\Message\BickMessageFaultInterface;
use Bick\Message\BickMessageInterface;
use Bick\Message\BickMessageTranslatorInterface;
use Bick\Storage\MessageFaultAnalyseInterface;
use Bick\Storage\MessagePersistenceInterface;
use Bick\Storage\PersistenceAdapterInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class BickConsumer
 * @package Bick\Service\Consumer
 */
abstract class BickConsumer implements
    BickConsumerInterface,
    BickConnectionShutdownInterface,
    MessagePersistenceInterface,
    MessageFaultAnalyseInterface
{

    /**
     * Set to true if you'd
     * like to use persistence
     *
     * @var bool
     */
    protected $persist = true;

    /**
     * Contains a bick message translator interface
     * implementation that receives a raw AMQPMessage
     * and must return a bick message interface
     *
     * @var BickMessageTranslatorInterface
     */
    private $translator;

    /**
     * @var BickConnection
     */
    private $connection;

    /**
     * @var int
     */
    private $status;

    /**
     * Contains a persistence adapter
     *
     * @var PersistenceAdapterInterface
     */
    private $adapter;

    /**
     * Contains a fault
     * mixed variable
     *
     * @var BickMessageFaultInterface
     */
    protected $fault;

    /**
     * @var array
     * [
     *  'channel' => AMQPChannel,
     *  'deliveryTag' => '',
     *  'message' => BickMessageInterface
     * ]
     */
    private $messageInfo;

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function __construct(BickConnection $connection)
    {
        $this->connection = $connection;

        register_shutdown_function([$this, 'close']);
    }

    /**
     * @param BickMessageTranslatorInterface $translator
     */
    public function setTranslator(BickMessageTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function consume(string $queue)
    {
        $this->connection->getChannel()
            ->basic_consume(
                $queue,
                self::DEFAULT_CONSUMER_TAG,
                false,
                false,
                false,
                false,
                [$this, 'transform']
            );

        while (count($this->connection->getChannel()->callbacks)) {
            try {
                $this->connection->getChannel()->wait();
            } catch (\Exception $exception) {
                throw new BickException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
    }

    /**
     * @param AMQPMessage $msg
     */
    public function transform(AMQPMessage $msg)
    {
        $this->messageInfo = [
            'channel' => $msg->delivery_info['channel'],
            'deliveryTag' => $msg->delivery_info['delivery_tag'],
            'message' => $this->translator->translate($msg),
        ];

        //Set status
        $this->status = $this->process($this->messageInfo['message']);

        $this->finish();
    }

    /**
     * @inheritdoc
     */
    abstract public function process(BickMessageInterface $message): int;

    /**
     * @inheritdoc
     */
    public function finish(): void
    {
        switch ($this->status) {
            default:
            case BickMessageInterface::MESSAGE_ACK:
                //Ack message
                $this->persist($this->messageInfo['message']);
                $this->messageInfo['channel']->basic_ack(
                        $this->messageInfo['deliveryTag']
                    );
                break;
            case BickMessageInterface::MESSAGE_NACK:
                if ($this->fault instanceof BickMessageFaultInterface) {
                    $this->analyse($this->messageInfo['message'], $this->fault);
                }
                $this->messageInfo['channel']->basic_nack(
                    $this->messageInfo['deliveryTag']
                );
                break;
            case BickMessageInterface::MESSAGE_NACK_REQUEUE:
                $this->messageInfo['channel']->basic_nack(
                    $this->messageInfo['deliveryTag'],
                    false,
                    true
                );
                break;
        }
    }

    /**
     * @param PersistenceAdapterInterface $adapter
     *
     * @return BickConsumerInterface
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $adapter): BickConsumerInterface
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
     * @param BickMessageInterface $message
     */
    public function persist(BickMessageInterface $message): void
    {
        if (false === $this->persist) {
            return;
        }

        if (!is_object($this->getPersistenceAdapter())) {
            return;
        }

        $this->getPersistenceAdapter()->update($message);
    }

    /**
     * @param BickMessageInterface $message
     * @param BickMessageFaultInterface $fault
     */
    public function analyse(BickMessageInterface $message, BickMessageFaultInterface $fault): void
    {
        if (false === $this->persist) {
            return;
        }

        if (!is_object($this->getPersistenceAdapter())) {
            return;
        }

        $this->getPersistenceAdapter()->analyse($message, $fault);
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        $this->connection->close();
    }
}
