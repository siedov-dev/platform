<?php


namespace App\Redis\Transaction;


use App\Redis\RedisArrayStorageInterface;

/**
 * @deprecated
 */
interface TransactionStorageInterface extends RedisArrayStorageInterface
{
    public function getByTransactionId($transaction_id);
    public function transactionExist($transaction_id);
}
