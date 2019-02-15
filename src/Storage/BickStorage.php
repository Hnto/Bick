<?php

namespace Bick\Storage;

use Bick\Batch\BickBatchInterface;
use Bick\Message\BickMessageFaultInterface;
use Bick\Message\BickMessageInterface;

class BickStorage implements PersistenceAdapterInterface
{
    /**
     * @var \PDO
     */
    private $adapter;

    /**
     * BickStorage constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->adapter = $pdo;
    }

    /**
     * @param BickMessageInterface $message
     * @return void
     */
    public function persist(BickMessageInterface $message): void
    {
        $i = 1;
        //Insert into batch
        $this->adapter
            ->prepare('
                insert into bick_batch (id) 
                values (:id) 
                on duplicate key update messages = messages + :message
            ')
            ->execute([
                ':id' => $message->getBatchUuid(),
                ':message' => $i
            ]);

        //Insert into message
        $this->adapter
            ->prepare('
              insert into bick_message (id, message, batchId) values (:id, :message, :batchId)
            ')
            ->execute([
                ':id' => $message->getUuid(),
                ':message' => json_encode($message),
                ':batchId' => $message->getBatchUuid()
            ]);
    }

    /**
     * @param BickMessageInterface $message
     *
     * @return void
     */
    public function update(BickMessageInterface $message): void
    {
        //Update message
        $this->adapter
            ->prepare('
                update bick_message set status = :status
                where id = :uuid
            ')
            ->execute([
                ':status' => BickMessageInterface::STATUS_DONE,
                ':uuid' => $message->getUuid()
            ]);

        //Check if all messages of batch have been completed
        $statement = $this->adapter
            ->prepare('
                select
                (select count(id) as amount from bick_message where batchId = :batchId) as amountOfMessages,
                (select count(id) as amount from bick_message where batchId = :batchId and status = :statusDone) as amountOfFinishedMessages
                from bick_message where batchId = :batchId limit 1;
            ');
        $statement->execute([
            ':batchId' => $message->getBatchUuid(),
            ':statusDone' => BickMessageInterface::STATUS_DONE
        ]);
        $status = $statement->fetchObject();

        //Update the batch
        if ((int) $status->amountOfMessages === (int) $status->amountOfFinishedMessages) {
            //Update batch
            $this->adapter
                ->prepare('
                update bick_batch set status = :status
                where id = :uuid
            ')
                ->execute([
                    ':status' => BickBatchInterface::BATCH_STATUS_DONE,
                    ':uuid' => $message->getBatchUuid()
                ]);
        }
    }

    /**
     * @param BickMessageInterface $message
     * @param BickMessageFaultInterface $fault
     *
     * @return void
     */
    public function analyse(BickMessageInterface $message, BickMessageFaultInterface $fault): void
    {
        //Insert into analysis storage
        $this->adapter
            ->prepare('
                insert into bick_analyse (message, fault) 
                values (:message, :fault) 
            ')
            ->execute([
                ':message' => json_encode($message),
                ':fault' => json_encode($fault)
            ]);
    }
}
