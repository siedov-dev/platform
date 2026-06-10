<?php


namespace App\Redis\Transaction;

use App\Redis\RedisArrayStorage;

/**
 * @deprecated
 */
class RedisTransactionStorage extends RedisArrayStorage implements TransactionStorageInterface
{
    protected int $user_id;
    protected string $storage_type;

    public function getByTransactionId($transaction_id)
    {
        return $this->findFirst(['transaction_id' => $transaction_id]);
    }

    public function transactionExist($transaction_id):bool
    {
        return $this->findFirst(['transaction_id' => $transaction_id]) !== null;
    }
}
