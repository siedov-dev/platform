<?php


namespace App\Redis;


use Illuminate\Support\Facades\Redis;

/**
 * Class RedisArrayStorage
 * @package App\Redis
 * @deprecated
 */
class RedisArrayStorage implements RedisArrayStorageInterface
{
    protected array $all_data = [];
    protected int $max_count = 50;

    protected int $user_id;
    protected string $storage_type;
    protected bool $autosave;

    public function __construct(int $user_id, string $storage_type, int $max_count = 200, bool $autosave = true)
    {
        $this->user_id = $user_id;
        $this->storage_type = $storage_type;
        $this->max_count = $max_count;
        $this->autosave = $autosave;

        $data = Redis::get('arstorage-' . $user_id . '-type-' . $storage_type);
        if (!empty($data) && is_array(json_decode($data, true))) {
            $this->all_data = json_decode($data, true);
        }
    }

    /**
     * get fresh data from memory
     */
    public function refresh()
    {
        $data = Redis::get('arstorage-' . $this->user_id . '-type-' . $this->storage_type);
        if (!empty($data) && is_array(json_decode($data, true))) {
            $this->all_data = json_decode($data, true);
        }
    }

    /**
     * get all data array
     * @return array
     */
    public function all(): array
    {
        return $this->all_data;
    }

    /**
     * get all data array as laravel collection
     * @return \Illuminate\Support\Collection
     */
    public function allCollection(): \Illuminate\Support\Collection
    {
        return collect($this->all());
    }

    /**
     * add array to storage
     * @param array $transaction data to store
     * @return $this
     */
    public function add(array $transaction): RedisArrayStorage
    {
        array_unshift($this->all_data, $transaction);
        if (count($this->all_data) > $this->max_count) {
            $this->all_data = array_slice($this->all_data, 0, $this->max_count);
//            array_pop($this->all_data);
        }
        if ($this->autosave) $this->save();
        return $this;
    }

    /**
     * add array to storage ad set 'id' field
     * @param array $transaction
     * @return array
     */
    public function addGenId(array $transaction): array
    {
        $transaction['id'] = uniqid(time());
        $this->add($transaction);
        if ($this->autosave) $this->save();
        return $transaction;
    }

    /**
     * get array by 'id' field
     * @param $id
     * @return mixed|null
     */
    public function getById($id): ?array
    {
        return $this->findFirst(['id' => $id]);
    }

    /**
     * get first array fits all conditions
     * @param array $conditions conditions array (['id' => 1, 'type' => 'test'])
     * @return mixed|null
     */
    public function findFirst(array $conditions): ?array
    {
        foreach ($this->all_data as $transaction) {
            $found = false;
            foreach ($conditions as $key => $val) {
                if (!array_key_exists($key, $transaction) || $transaction[$key] != $val) {
                    $found = false;
                    break;
                }
                $found = true;
            }
            if ($found) return $transaction;
        }
        return null;
    }

    /**
     * returns if there is an array with field fits conditions specified
     * @param array $conditions conditions array (['id' => 1, 'type' => 'test'])
     * @return bool
     */
    public function exists(array $conditions): bool
    {
        return $this->findFirst($conditions) !== null;
    }

    /**
     * updates data in arrays fit conditions
     * @param array $conditions conditions array (['id' => 1, 'type' => 'test'])
     * @param array $values data to update array (['type' => 'updated',...])
     * @return $this
     */
    public function update(array $conditions, array $values): RedisArrayStorage
    {
        foreach ($this->all_data as $idx => $transaction) {
            $found = false;
            foreach ($conditions as $key => $val) {
                if (!array_key_exists($key, $transaction) || $transaction[$key] != $val) {
                    $found = false;
                    break;
                }
                $found = true;
            }
            if ($found) {
                foreach ($values as $k => $v) {
                    $this->all_data[$idx][$k] = $v;
                }
            }
        }
        $this->all_data = array_values($this->all_data);
        if ($this->autosave) $this->save();
        return $this;
    }

    /**
     * delete arrays fits conditions
     * @param array $conditions conditions array (['id' => 1, 'type' => 'test'])
     * @return $this
     */
    public function delete(array $conditions): RedisArrayStorage
    {
        foreach ($this->all_data as $idx => $transaction) {
            $found = false;
            foreach ($conditions as $key => $val) {
                if (!array_key_exists($key, $transaction) || $transaction[$key] != $val) {
                    $found = false;
                    break;
                }
                $found = true;
            }
            if ($found) unset($this->all_data[$idx]);
        }
        $this->all_data = array_values($this->all_data);
        if ($this->autosave) $this->save();
        return $this;
    }

    /**
     * clears storage
     * @return $this
     */
    public function deleteAll(): RedisArrayStorage
    {
        $this->all_data = [];
        if ($this->autosave) $this->save();
        return $this;
    }

    /**
     * manually save storage to memory (if autosave = false)
     * @return $this
     */
    public function save(): RedisArrayStorage
    {
        Redis::set('arstorage-' . $this->user_id . '-type-' . $this->storage_type, json_encode($this->all_data));
        return $this;
    }
}
