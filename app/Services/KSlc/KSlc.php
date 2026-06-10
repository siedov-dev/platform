<?php

namespace App\Services\KSlc;

use App\Helpers\Hlog;
use App\Models\Slc\SlcDailyReward;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcTaskType;
use App\Models\Slc\UserTasks\UserDailyTask;
use App\Models\Slc\UserTasks\UserTaskType;
use App\Models\Slc\UserTasks\UserTodaysReward;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class KSlc
{
    private static ?KSlc $_instance = null;
    private string $token = "";
    private string $host = "http://127.0.0.1:23104";

    private function __construct()
    {
        $this->token = config('services.ServicesToken');
        $this->host = config('services.kSlcHost');
    }

    public static function getInstance(): KSlc
    {
        if (self::$_instance === null) {
            self::$_instance = new KSlc();
        }
        return self::$_instance;
    }

    public function ping()
    {
        return Http::withToken($this->token)->acceptJson()->get($this->host, [])->body();
    }

    public function getSlcBalance(int $user_id, int $currency_id): int
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "currency_id" => $currency_id,
            ];
            $resp = json_decode($this->request("getBalance", $payload), true);
            if (empty($resp || $resp['cd'] !== 200)) return 0;
            return intval($resp['amount'] ?? 0);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function addSlcBalance(int $user_id, int $currency_id, int $amount, string $comment = ""): int
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "currency_id" => $currency_id,
                "amount" => $amount,
                "comment" => $comment
            ];
            $resp = json_decode($this->request("addBalance", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return 0;
            return intval($resp['amount'] ?? 0);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function remSlcBalance(int $user_id, int $currency_id, int $amount, string $comment = ""): ?int
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "currency_id" => $currency_id,
                "amount" => $amount,
                "comment" => $comment
            ];
            $resp = json_decode($this->request("remBalance", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error remSlcBalance");
            };
            return intval($resp['amount'] ?? null);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getStartTaskStatus(int $user_id, string $task_type): ?int
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_type" => $task_type
            ];
            $resp = json_decode($this->request("getStartTaskStatus", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error getStartTaskStatus");
            };
            return intval($resp['status'] ?? null);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function setStartTaskStatus(int $user_id, string $task_type, int $status): bool
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_type" => $task_type,
                "status" => $status,
            ];
            $resp = json_decode($this->request("setStartTaskStatus", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error setStartTaskStatus");
            };
            return true;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function getStartTaskCompletedCount(int $user_id): ?int
    {
        try {
            $payload = [
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("getStartTaskCompletedCount", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error getStartTaskCompletedCount");
            };
            return intval($resp['count'] ?? null);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getStartTaskRewardedCount(int $user_id): ?int
    {
        try {
            $payload = [
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("getStartTaskRewardedCount", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error getStartTaskRewardedCount");
            };
            return intval($resp['count'] ?? null);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function dailyTaskGetTotalRewardedCount(int $user_id): ?int
    {
        try {
            $payload = [
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("dailyTaskGetTotalRewardedCount", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTotalRewardedCount");
            };
            return intval($resp['total_rewarded'] ?? null);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function DailyLoginGetRewardsForDay(int $day): Collection
    {
        try {
            $payload = [
                "week_day" => $day,
            ];
            $resp = json_decode($this->request("dailyLoginGetRewardsForDay", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyLoginGetRewardsForDay");
            };
            $rewards = [];
            foreach ($resp['rewards'] as $reward) {
                $rewards[] = new SlcDailyReward(
                    id: $reward['id'],
                    day: $reward['day'],
                    type: SlcItemType::from($reward['type']),
                    bonus_type: $reward['bonus_type']['Int64']?? 0,
                    params: $reward['params']['String']?? "",
                    allowed_games: $reward['allowed_games']['String']?? "",
                    amount: $reward['amount']['Int64']?? 0,
                );
            }
            return collect($rewards);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return collect([]);
    }

    public function getUserDailyTasks(int $user_id, string $currency): Collection
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "currency" => $currency,
            ];
            $resp = json_decode($this->request("dailyTaskGetTodays", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTodays");
            };
            $tasks = [];
            foreach ($resp['tasks'] as $task) {
                $tasks[] = new UserDailyTask(
                    id: $task['id'],
                    group_id: $task['group_id']['Int64']?? 0,
                    user_id: $task['user_id'],
                    currency: $task['currency']['String']?? '',
                    type: UserTaskType::from($task['type']),
                    target_value: $task['target_value'],
                    float_param: $task['float_param'],
                    int_param: $task['int_param'],
                    string_param: $task['string_param'],
                    is_completed: $task['is_completed'],
                    is_rewarded: $task['is_rewarded'],
                );
            }
            return collect($tasks);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return collect([]);
    }

    public function getUserDailyTask(int $user_id, int $task_id): ?UserDailyTask
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskGetTask", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTask");
            };
                $data = $resp['task'];
                $task = new UserDailyTask(
                    id: $data['id'],
                    group_id: $data['group_id']['Int64']?? 0,
                    user_id: $data['user_id'],
                    currency: $data['currency']['String']?? '',
                    type: UserTaskType::from($data['type']),
                    target_value: $data['target_value'],
                    float_param: $data['float_param'],
                    int_param: $data['int_param'],
                    string_param: $data['string_param'],
                    is_completed: $data['is_completed'],
                    is_rewarded: $data['is_rewarded'],
                );
            return $task;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function setDailyTaskCompleted(int $user_id, int $task_id): bool
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskSetCompleted", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskSetCompleted");
            };
            return true;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function setDailyTaskRewarded(int $user_id, int $task_id): bool
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskSetRewarded", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskSetRewarded");
            };
            return true;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    /**
     * Retrieves the count of daily tasks completed and rewarded for a specific user.
     *
     * @param int $user_id The ID of the user.
     * @return array{completed: int, rewarded: int} An associative array containing:
     *               - 'completed': The number of tasks completed today.
     *               - 'rewarded': The number of tasks rewarded today.
     * On failure, both values default to 0.
     */
    public function getDailyTaskCompletedAndRewardedCount(int $user_id): array
    {
        try {
            $payload = [
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("dailyTaskGetTodaysCompletedCount", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTodaysCompletedCount");
            };
            return [
                'completed' => $resp['completed'] ?? 0,
                'rewarded' => $resp['rewarded'] ?? 0,
            ];

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [
            'completed' => 0,
            'rewarded' => 0,
        ];
    }

    public function getUserDailyTaskRewards(int $user_id, int $task_id): Collection
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskGetTaskRewards", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTaskRewards");
            };
            $rewards = [];
            foreach ($resp['rewards'] as $reward) {
                $rewards[] = new UserTodaysReward(
                    id: $reward['id'],
                    task_id: $reward['task_id'],
                    user_id: $reward['user_id'],
                    amount: $reward['amount'],
                    type: SlcItemType::from($reward['type']),
                    bonus_type: $reward['bonus_type']['Int64']?? 0,
                    params: $reward['params']['String']?? "",
                    allowed_games: $reward['allowed_games']['String']?? "",
                );
            }
            return collect($rewards);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return collect([]);
    }

    public function checkDailyTaskCompleted(int $user_id, int $task_id): bool
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskCheckTaskCompleted", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskCheckTaskCompleted");
            };
            return $resp['completed']?? false;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function getDailyTaskValues(int $user_id, int $task_id): array
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_id" => $task_id,
            ];
            $resp = json_decode($this->request("dailyTaskGetTaskValues", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskGetTaskValues");
            };
            return [
                'current_value' => $resp['current_value'] ?? 0,
                'goal_value' => $resp['goal_value'] ?? 0,
            ];

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [
            'current_value' => 0,
            'goal_value' => 0,
        ];
    }

    public function dailyTaskAddCurrentValue(int $user_id, UserTaskType $task_type, string $currency, int $value): ?string
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_type" => $task_type->value,
                "currency" => $currency,
                "value" => $value,
            ];
            $resp = json_decode($this->request("dailyTaskAddCurrentValue", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskAddCurrentValue");
            };
            return null;

        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    public function dailyTasksCheckTaskByType(int $user_id, UserTaskType $task_type, string $currency): void
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "task_type" => $task_type->value,
                "currency" => $currency,
            ];
            $resp = json_decode($this->request("dailyTaskCheckTaskByTypeAndCurrency", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error dailyTaskCheckTaskByTypeAndCurrency");
            };
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function reloadService(): ?string
    {
        try {
            $payload = [
            ];
            $resp = json_decode($this->request("serviceReload", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err']?? "Error reloadService");
            };
            return null;

        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    private function request(string $method, array $payload)
    {
        $endpoint = $this->host."/api/$method";
        $resp = Http::withToken($this->token)->acceptJson()->post($endpoint, $payload)->body();
        Log::info($resp);
        return $resp;
    }
}
