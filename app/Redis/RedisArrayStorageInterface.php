<?php


namespace App\Redis;


/**
 * @deprecated
 */
interface RedisArrayStorageInterface
{
    /**
     * get all data array
     * @return array
     */
    public function all();

    /**
     * get fresh data from memory
     */
    public function refresh();
    public function allCollection():\Illuminate\Support\Collection;
    public function add(array $transaction);
    public function addGenId(array $transaction);
    public function getById($id);
    public function update(array $conditions, array $values);
    public function findFirst(array $conditions);
    public function exists(array $conditions);
    public function delete(array $conditions);
    public function deleteAll();
    public function save();
}
