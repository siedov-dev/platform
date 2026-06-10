<?php


namespace App\Redis;


use App\Jobs\StoreGameTransactions;
use App\Models\GameTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated
 */
class DeferredModelWriter
{
    protected int $_max_cache;
    protected string $_alias;
    protected string $_model_class;

    public static function getForModel($model_class, $max_cached_elements = 50): DeferredModelWriter
    {
        return new self($model_class, $model_class, $max_cached_elements);
    }

    /**
     * @param string $alias redis id
     * @param string $model_class DB model class
     * @param int $max_cached_elements max items stored before write to DB
     */
    public function __construct(string $alias, string $model_class, int $max_cached_elements = 50)
    {
        $this->_alias = $alias;
        $this->_max_cache = $max_cached_elements;
        $this->_model_class = $model_class;
    }

    /**
     * store data to redis cache
     * @param $data
     * @return int
     */
    public function store(mixed $data): int
    {
        try {
            if (!is_string($data)) {
                $data = json_encode($data);
            }

            $size = Redis::lpush($this->_alias, $data);

            //store data to DB when reaches $_max_cache
            if ($this->_max_cache > 0 && $size >= $this->_max_cache) {
                StoreGameTransactions::dispatch();
//                $this->writeToDb();
            }

            return $size;

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        return 0;
    }

    /**
     * store up to _max_cache elements from redis to database
     */
    public function writeToDb(bool $save_all = false): void
    {
        try {

            DB::beginTransaction();
            $c = 1;
            while (($data = Redis::rpop($this->_alias)) && ($save_all || $c < $this->_max_cache)) {
                $data = json_decode($data, true);
                $model = new $this->_model_class;
                foreach ($data as $k => $v) {
                    $model[$k] = $v;
                }
                $model->save();
                $c++;
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::commit();
            Log::error($e->getMessage());
        }
    }

    public static function test(): void
    {
        $wr = DeferredModelWriter::getForModel(GameTransaction::class);
        $transaction = [
            'user_id' => 12,
            'bonus' => null,
            'action' => 'bet',
            'amount' => '100.32',
            'currency' => 'RUB',
            'game_id' => 'xxxgame',
            'session_id' => '123213',
            'transaction_id' => '321123',
            'type' => 'none',
            'system' => 'vegas',
            'date' => now()->format('Y-m-d H:i:s'),
            'last_bet_value' => 123.23,
            'last_bet_id' => 'testid',
            'gen_id' => 'test_gen_id',
        ];

        $wr->store($transaction);
        $wr->store($transaction);
        $wr->store($transaction);
        $wr->store($transaction);
        $wr->store($transaction);
        $wr->store($transaction);
        $wr->writeToDb();
    }
}
