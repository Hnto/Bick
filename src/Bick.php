<?php

namespace Bick;

use Bick\Binding\QueueBindExchangeSetup;
use Bick\Connection\BickConnection;
use Bick\Connection\BickConnectionSetup;
use Bick\Consumer\BickConsumer;
use Bick\Consumer\BickConsumerInterface;
use Bick\Exception\BickException;
use Bick\Exchange\ExchangeSetup;
use Bick\Message\BickMessageTranslator;
use Bick\Publisher\BickPublisherInterface;
use Bick\Queue\QueueSetup;
use PhpAmqpLib\Channel\AMQPChannel;

class Bick
{
    /**
     * @var BickConnectionSetup
     */
    private $setup;

    /**
     * Bick constructor.
     *
     * @param BickConnectionSetup $setup
     */
    public function __construct(BickConnectionSetup $setup)
    {
        $this->setup = $setup;
    }

    /**
     * @param array $setup
     * @throws BickException
     */
    public function setup(array $setup)
    {
        $connection = $this->connection();
        //queues
        foreach ($setup['queues'] as $queue) {
            $this->setupQueue($connection->getChannel(), $queue);
        }
        //exchanges
        foreach ($setup['exchanges'] as $exchange) {
            $this->setupExchange($connection->getChannel(), $exchange);
        }
        //bindings
        foreach ($setup['bindings'] as $binding) {
            $this->bindQueueToExchange(
                $connection->getChannel(),
                $binding
            );
        }

        $connection->close();
    }

    /**
     * Create a new consumer
     *
     * @param string $name
     * @return BickConsumerInterface
     *
     * @throws BickException
     */
    public function consumer(string $name): BickConsumerInterface
    {
        if (!class_exists($name)) {
            throw new BickException('Consumer ' . $name . ' not found');
        }

        if (!array_key_exists(BickConsumerInterface::class, class_implements($name))) {
            throw new BickException('Consumer ' . $name . ' does not implement the correct interface');
        }

        $consumer = new $name($this->connection());

        //If bick consumer, set the standard translator
        if ($consumer instanceof BickConsumer) {
            $consumer->setTranslator(new BickMessageTranslator());
        }

        return $consumer;
    }

    /**
     * Create a new publisher
     *
     * @param string $name
     *
     * @return BickPublisherInterface
     *
     * @throws BickException
     */
    public function publisher(string $name): BickPublisherInterface
    {
        if (!class_exists($name)) {
            throw new BickException('Publisher ' . $name . ' not found');
        }

        if (!array_key_exists(BickPublisherInterface::class, class_implements($name))) {
            throw new BickException('Publisher ' . $name . ' does not implement the correct interface');
        }

        return new $name($this->connection());
    }

    /**
     * Returns a new BickConnection
     * with a new AMQPStreamConnection
     * and AMQPChannel
     *
     * @return BickConnection
     * @throws BickException
     */
    private function connection(): BickConnection
    {
        return new BickConnection(
            $this->setup->getHost(),
            $this->setup->getPort(),
            $this->setup->getUser(),
            $this->setup->getPass(),
            $this->setup->getVhost()
        );
    }

    /**
     * @param AMQPChannel $channel
     * @param QueueSetup $queueSetup
     * @return void
     */
    private function setupQueue(AMQPChannel $channel, QueueSetup $queueSetup)
    {
        $channel
            ->queue_declare(
                $queueSetup->getName(),
                $queueSetup->getPassive(),
                $queueSetup->getDurable(),
                $queueSetup->getExclusive(),
                $queueSetup->getAutoDelete(),
                $queueSetup->getNoWait(),
                $queueSetup->getArguments(),
                $queueSetup->getTicket()
            );
    }

    /**
     * @param AMQPChannel $channel
     * @param ExchangeSetup $exchangeSetup
     *
     * @return void
     */
    private function setupExchange(AMQPChannel $channel, ExchangeSetup $exchangeSetup)
    {
        $channel
            ->exchange_declare(
                $exchangeSetup->getName(),
                $exchangeSetup->getType(),
                $exchangeSetup->getPassive(),
                $exchangeSetup->getDurable(),
                $exchangeSetup->getAutoDelete(),
                $exchangeSetup->getNoWait(),
                $exchangeSetup->getArguments(),
                $exchangeSetup->getTicket()
            );
    }

    /**
     * @param AMQPChannel $channel
     * @param QueueBindExchangeSetup $queueBindExchangeSetup
     */
    private function bindQueueToExchange(AMQPChannel $channel, QueueBindExchangeSetup $queueBindExchangeSetup) {
        $channel
            ->queue_bind(
                $queueBindExchangeSetup->getQueue(),
                $queueBindExchangeSetup->getExchange(),
                $queueBindExchangeSetup->getRoutingKey()
            );
    }
}
