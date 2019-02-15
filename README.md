# Bick
Bick is a RabbitMQ wrapper built with the official php-amqlib library.
Besides a wrapper for simple setup, publish and consume, Bick can also be used
for batch publshing messages, keeping track of them etc.

#
## Install
```bash
composer require hnto/bick
```

#
## Possibilities
- simple setup, publish and consume using one Bick class
- batch publishing messages
    - stored in a data storage of your choice
    - prioritizing messages
    - keeping track of messages
- store faulty messages in a data storage of your choice for further research

#
## Setup
- Instantiate a `Bick` class by providing a `BickConnectionSetup`
    - The `BickConnectionSetup` requires a config with the values:
        - host
        - port
        - user
        - pass
        - vhost (default "/")
    - Execute the `setup` method by providing the queues, exchanges and bindings
    - `Bick` throws an exception on a connection fault
    
#### Example
```php
try {
    $bick = new Bick(new BickConnectionSetup([
        'host' => 'localhost',
        'port' => '5672',
        'user' => 'guest',
        'pass' => 'guest'
    ]));

    $bick->setup([
        'queues' => [
            new QueueSetup('mailing'),
            new QueueSetup('process-file')
        ],
        'exchanges' => [
            new ExchangeSetup('default')
        ],
        'bindings' => [
            new QueueBindExchangeSetup('mailing', 'default', 'mail'),
            new QueueBindExchangeSetup('process-file', 'default', 'process-csv'),
            new QueueBindExchangeSetup('process-file', 'default', 'process-image'),
            new QueueBindExchangeSetup('process-file', 'default', 'process-pdf'),
        ]
    ]);
} catch (BickException $exception) {
}
```

## Publish message
The `Bick` object can be used to retrieve a `BickPublisherInterface` and publish a message.
The `publish` method inside the publisher requires a `BickMessageInterface` object and 
throws an exception on an invalid `BickMessage`

**Due note: when using the default `BickPublisher` class and you don't want to use persistence, set it to false using the publisher method `BickPublisher::persistMessages(false)`.**

#### Example
```php
try {
    $publisher = $bick->publisher(MyPublisher::class);
    
    //Set the persistence adapter
    $publisher->setPersistenceAdapter($adapter);
    
    //Or if you don't want to persist
    $publisher->persistMessages(false);
    
    //When using the default BickPublisher object
    //you can set a set of available publishing options
    //Available publishing options
    $publisher->setPublishingOptions(
        //With this option you provide a valid callback that will be
        //executed upon an ACK from the broker when publishing a message.
        //Due note: this does not mean that the message was successfully routed
        //to a queue. Only that it was accepted by the broker.
        'callback_ack' => function(AMQPMessage $msg),
        
        //With this option you provide a valid callback that will be
        //executed upon a NACK from the broker when publishing a message.
        //A NACK happens in exceptional cases that a message was
        //rejected by the broker.
        'callback_nack' => function(AMQPMessage $msg),
        
        //With this option you provide a valid callback that will be
        //executed when the broker returns a message.
        //This usually happens if the message was, for some reason,
        //unroutable. This means that the message could not be routed
        //to a certain queue. This callback receives more info that the two above.
        //You receive a reply code, a reply text, the exchange the message
        //was published to, the routing key used and the AMQPMessage itself.
        callback_return => function($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $msg),
    );
    
    $publisher->publish(new BickMessage([
        'body' => ['headers' => [], 'subject' => 'test', 'body' => '<html><body>Test</body></html>'],
        'meta' => ['mail' => 'test@example.org'],
        'exchange' => 'default',
        'routingKey' => 'mail'
    ]));
} catch (BickException $exception) {
}
```

## Publish messages (batch)
The `Bick` object also offers the option to publish messages in a batch. 
The logic is the same as when publishing one message. The difference is that you send an array of `BickMessage` objects.
#### Example
```php
try {
    $publisher = $bick->publisher();
    
    //Set the persistence adapter
    $publisher->setPersistenceAdapter($adapter);
    
    //Or if you don't want to persist
    $publisher->persistMessages(false);
    
    $publisher->publishBatch([
        new BickMessage([
            'body' => ['users' => [1, 2, 33]],
            'meta' => ['mail' => 'info@users.com'],
            'queue' => 'process-file',
            'exchange' => 'default',
            'routingKey' => 'process-csv'
        ]),
        new BickMessage([
            'body' => ['body' => 'test'],
            'meta' => ['mail' => 'info@users.com'],
            'queue' => 'mailing',
            'exchange' => 'default',
            'routingKey' => 'mail'
        ])
    ]);
} catch (BickException $exception) {
}
```
## Consuming messages
For consuming messages you must create your own consumer class. This class must implement `BickConsumerInterface` and `BickConnectionShutdownInterface`.
For a more easy setup, you can also extend the abstract `BickConsumer`. The only method you are required to implement is
the `process` method. This method is given a `BickMessageInterface`. Within this method you are required to return a status. 
These statuses can be found within the `BickMessageInterface` as constants. A consumer class can be retrieved by executing the `Bick` object method `consumer`. 
To start consuming messages execute the `consume` method inside the consumer class.

**Due note: when using the `BickConsumer` abstract, set the protected member var `$persist` to `false` if you don't want to use persistence**

#### Example
```php
//MailingConsumer
class MyConsumer extends BickConsumer
{
    /**
     * Must contain the queue name
     *
     * @var string
     */
    protected $queue = 'mailing';
    
    protected $persist = false;

    /**
     * @inheritdoc
     */
    public function process(BickMessageInterface $message): int
    {
        //Do something with the message

        return BickMessage::MESSAGE_ACK;
    }
}

//Consume messages
$consumer = $bick->consumer(MyConsumer::class);

//Set the persistence adapter (not requred if $persist is set to false)
$consumer->setPersistenceAdapter($adapter);

//Consume
$consumer->consume('my-queue');
```

When using the `BickConsumer` you can "translate" a raw AMQP message to a message of your own that implements `BickMessageInterface`.
This way you have full control over what goes inside the message body and further info. Upon requesting the default `BickConsumer` the 
standard translator is set in order to create a default `BickMessage` object. Replacing the default translator can be done with the `setTranslator` method. 

```php
$consumer->setTranslator(new MyTranslator());

class MyTranslator implements BickMessageTranslatorInterface
{
    public function translate(AMQPMesage $msg): BickMessageInterface {
        //Do your own thing with the messsage
        //and return a valid BickMessageInterface object
    }
}
```

## Message persistence
##### Persisting messages in order to keep track of them
In Bick you can persist messages into a data storage by providing a data storage adapter object. 
This adapter can be whatever you'd like (MySQL, Redis, File). 
The `BickPublisher` and `BickConsumer` objects already have the implemention available for persisting messages into a data storage using the method `persist` of your adapter. 
The only thing they require is an `Adapter` variable that you set in the `setAdapter` method of these classes. 
This adapter must implement the `PersistenceAdapterInterface`. What and how you save this data, is up to you. 
### Example
```php
    //PersistenceAdapterInterface
    ...
    public function persist(BickMessage $message): void;
    ...
    public function update(BickMessage $message): void;
    ...
    public function analyse(BickMessage $message, BickMessageFault $fault): void;
    ...
    
    //BickPublisher
    public function persist(BickMessage $message)
    {
        ....
        //Your adapter method "persist" will receive a BickMessage object
        //persist is used to insert a new message into your datastorage
        $this->getPersistenceAdapter()->persist($message);
    }
    
    //BickConsumer
    public function persist(BickMessage $message): void
    {
        ...
        Your adapter method "update" will receive a BickMessage object
        //update is used to update an existing message in your datastorage
        $this->getPersistenceAdapter()->update($message);
    }
}
```
##### Persisting messages after a NACK in order to analyse them
In Bick you can persist a message that was a "NACK" and provide optional information regarding the fault. In order to implement this functionality your consumer must implement the interface `MessageFaultAnalyseInterface`.
This interface requires that you define a method called `analyse` which requires a `BickMessage` object.
Furthermore you must assign a `BickMessageFault` object to the member var `protected $fault`. This fault is sent to the data storage along with the message; this class implements `JsonSerializable`. 
The `BickConsumer` has this implementation by default. So if you extend it you're messages are stored automatically into a data storage when returning a `NACK` status.
### Example
```php
//MessageFaultAnalyseInterface
...
public function analyse(BickMessage $message, BickMessageFault $fault): void;

//MailingConsumer
class MailingConsumer extends BickConsumer {
    ...
    public function process(BickMessage $message): int
    {
        //Do something with the message
        //Fault message (string|array), fault code (integer)
        $this->fault = new BickMessageFault('fault message', 1);

        return BickMessage::MESSAGE_NACK;
    ...
```

## Bick adapter
Bick has its own adapter for saving/tracking the messages. This is a PDO adapter connecting to a relational database of your choice (MySQL/MariaDB/Postgre).
### SQL tables
```sql
-- Create syntax for TABLE 'bick_batch'
CREATE TABLE `bick_batch` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `messages` int(10) DEFAULT '1',
  `status` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'bick_message'
CREATE TABLE `bick_message` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `message` text,
  `status` int(1) NOT NULL DEFAULT '0',
  `batchId` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `batchId` (`batchId`),
  CONSTRAINT `batchId` FOREIGN KEY (`batchId`) REFERENCES `bick_batch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'bick_analyse'
CREATE TABLE `bick_analyse` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `fault` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Example
```php
//Bick\Service\Storage\BickStorage
    ...
    //This method is executed upon publishing a message
    public function persist(BickMessage $message)
    {
        ...
        //Insert into batch
        ...
        //Insert into message
        ...
    
    //This method is executed upon consuming a queue
    public function update(BickMessage $message)
    {
        //Update message
        ...
        //Check if all messages of batch have been completed
        ...
        //Update the batch
        ...
```
You can implement the Bick adapter in this way
```php
$dsn = 'mysql:host=host;dbname=name';
$username = 'username';
$password = 'password';
//Optional options
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

$dbh = new PDO($dsn, $username, $password, $options);

//Instantiate the adapter and give it a PDO object
$adapter = new \Bick\Service\Storage\BickStorage($dbh);

//Set the adapter in the publisher
$bick->publisher(BickPublisher::class)
    ->setPersistenceAdapter($adapter);

//Set the adapter in the consumer
$bick->consumer(MyConsumer::class)
    ->setPersistenceAdapter($adapter);
```

## Facts
- Upon instantiating a `Bick` object the config is saved
    - The connection to RabbitMQ is not created until an action is requested
- Upon setup a connection is opened and a channel is requested
    - The channel and connection are closed afterwards
- Upon publishing a message a connection is opened and a channel is requested
    - The channel and connection are closed afterwards
- Upon consuming a queue a connection is opened and a channel is requested
    - The channel and connection are **not** closed afterwards. These will be closed if the consumer is stopped. 

## To do
- Implement sending an event or notification upon a finished message (in a batch) or a faulty one
- Prioritizing messages in a batch
- Dashboard for viewing fault messages, edit them and republish them accordingly
