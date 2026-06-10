<?php

namespace App\CustomerIo;

use App\Helpers\Hlog;
use App\Jobs\SyncCustomerIoJob;
use App\MUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Customer io service api
 * @docs https://docs.customer.io/api/track/?region=eu#operation/batch
 */
class CustomerIo
{
    private string $siteId = "";
    private string $key = "";
    private string $api_key = "";
    private string $track_endpoint = "https://track-eu.customer.io/api/";
    private string $api_endpoint = "https://api-eu.customer.io/";
    private bool $enabled = false;

    const CUSTOM_EMAIL_ID = 2;

    private static ?CustomerIo $_instance = null;

    public function __construct()
    {
        $this->key = config('services.customerIo.key');
        $this->siteId = config('services.customerIo.siteId');
        $this->api_key = config('services.customerIo.apiKey');
        $this->track_endpoint = config('services.customerIo.track_endpoint');
        $this->api_endpoint = config('services.customerIo.api_endpoint');
        $this->enabled = config('services.customerIo.enabled');
    }

    /**
     * create|update user
     * @param string $id
     * @param array $attributes
     * @return array|mixed
     */
    public function identity(string $id, array $attributes)
    {
        if (!$this->enabled) return;
        $method = "v2/entity";
        $payload = $this->identityRequestData($id, $attributes);

        $resp = Http::asJson()->acceptJson()
            ->withBasicAuth($this->siteId, $this->key)
            ->post($this->track_endpoint.$method, $payload);
        return $resp->json();
    }

    public function identityRequestData(string $id, array $attributes): array
    {
        if (!$this->enabled) return [];
        return [
            'type' => 'person',
            'identifiers' => [
                'id' => $id,
            ],
            'action' => 'identify',
            'attributes' => $attributes,
        ];
    }

    /**
     * @param array $batch array of RequestData
     * @return array|mixed
     */
    public function batch(array $batch)
    {
        if (!$this->enabled) return [];
        $method = "v2/batch";
        $payload = [
            'batch' => $batch,
        ];

        $resp = Http::asJson()->acceptJson()
            ->withBasicAuth($this->siteId, $this->key)
            ->post($this->track_endpoint.$method, $payload);
        return $resp->json();
    }

    /**
     * list segments
     * @return array|mixed
     */
    public function segments()
    {
        if (!$this->enabled) return [];
        $method = "v1/segments";
        $resp = Http::acceptJson()
            ->withToken($this->api_key)
            ->get($this->api_endpoint.$method);
        return $resp->json();
    }

    /**
     * create segment
     * @param string $name
     * @param string $description
     * @return array|mixed
     */
    public function segmentCreate(string $name, string $description)
    {
        if (!$this->enabled) return [];
        $method = "v1/segments";
        $payload = [
            'segment' => [
                'name' => $name,
                'description' => $description,
            ],
        ];
        Log::info('segmentCreate ', $payload);
        $resp = Http::acceptJson()
            ->asJson()
            ->withToken($this->api_key)
            ->post($this->api_endpoint.$method, $payload);
        Log::info('segmentCreate '. $resp->body());
        return $resp->json();
    }

    /**
     * add users to segment
     * @param string $segment_id
     * @param array $ids limit 1000 ids
     * @return bool success
     */
    public function segmentAddUserIds(string $segment_id, array $ids): bool
    {
        if (!$this->enabled) return false;
        $method = "v1/segments/$segment_id/add_customers";
        $payload = [
            'segment_id' => $segment_id,
            'ids' => $ids,
        ];

        $resp = Http::asJson()->acceptJson()
            ->withBasicAuth($this->siteId, $this->key)
            ->post($this->track_endpoint.$method, $payload);
        Log::info('segmentAddUserIds '. $resp->body());
        return $resp->status() === 200;
    }

    /**
     * remove users from the segment
     * @param string $segment_id
     * @param array $ids limit 1000 ids
     * @return bool success
     */
    public function segmentRemoveUserIds(string $segment_id, array $ids): bool
    {
        if (!$this->enabled) return false;
        $method = "v1/segments/$segment_id/remove_customers";
        $payload = [
            'segment_id' => $segment_id,
            'ids' => $ids,
        ];

        $resp = Http::asJson()->acceptJson()
            ->withBasicAuth($this->siteId, $this->key)
            ->post($this->track_endpoint.$method, $payload);
        Log::info('segmentRemoveUserIds '. $resp->body());
        return $resp->status() === 200;
    }

    /**
     * get users ids in the segment
     * @param string $segment_id
     * @return array
     */
    public function segmentGetUserIds(string $segment_id): array
    {
        if (!$this->enabled) return [];
        $method = "v1/segments/$segment_id/membership";
        $ids = [];
        $resp = Http::asJson()->acceptJson()
                ->withToken($this->api_key)
                ->get($this->api_endpoint.$method);
        Log::info('segmentGetUserIds '. $resp->body());
        if (!$resp->successful()) {
            Log::error('error get users in segment'.$resp->status());
            return [];
        }
        $data = $resp->json();
        $ids = array_merge($ids, $data['ids']);
        // while there's next page
        while (!empty($data['next'])) {
            $m = $method.'?start='.$data['next'];
            $resp = Http::asJson()->acceptJson()
                ->withToken($this->api_key)
                ->get($this->api_endpoint.$m);
            Log::info('segmentGetUserIds '. $resp->body());
            if (!$resp->successful()) {
                Log::error('error get users in segment'.$resp->status());
                return [];
            }
            $ids = array_merge($ids, $data['ids']);
            $data = $resp->json();
        }
        return array_map('intval', $ids);
    }

    /**
     * delete the segment
     * @param string $segment_id
     * @return bool
     */
    public function segmentDelete(string $segment_id): bool
    {
        if (!$this->enabled) return false;
        $method = "v1/segments/$segment_id";
        $resp = Http::acceptJson()
            ->asJson()
            ->withToken($this->api_key)
            ->delete($this->api_endpoint.$method);
        return $resp->status() === 204;
    }

    /** send email to user
     * @param string $from
     * @param string $subject
     * @param string $to
     * @param string $body
     * @return array
     */
    public function sendEmail(string $from, string $subject, string $to, string $body): array
    {
        if (!$this->enabled) return [];
        $method = "v1/send/email";
        $payload = [
            'transactional_message_id' => self::CUSTOM_EMAIL_ID,
            'to' => $to,
            'identifiers' => [
                'email' => $to,
            ],
            'body' => $body,
            'subject' => $subject,
            'from' => $from,
        ];
        $resp = Http::acceptJson()
            ->asJson()
            ->withToken($this->api_key)
            ->post($this->api_endpoint.$method, $payload);
        return $resp->json();
    }


    public function sendEvent(string $user_id, CioEventType $event, array $data = []): bool
    {
        if (!$this->enabled) {
            Log::info('customer not enabled, event '.$event->name, $data);
            return false;
        }
        try {
            $c_user = CustomerIoUser::where('user_id', $user_id)->first();
            // sync user if not exists
            if ($c_user === null) {
                $muser = MUser::find($user_id);
                CustomerIoUser::syncUser($muser);
            }
            $method = "v1/customers/$user_id/events";
            $payload = [
                'name' => $event->name,
                'id' => Str::uuid(),
                'type' => 'event',
                'timestamp' => time(),
            ];
            Log::info('CIO sendEvent '.$event->name, $data);
            if (!empty($data)) {
                $payload['data'] = $data;
            }
            $resp = Http::asJson()->acceptJson()
                ->withBasicAuth($this->siteId, $this->key)
                ->post($this->track_endpoint.$method, $payload);
            Log::info('CIO sendEvent resp:'. json_encode($resp->json()));
            Log::info('CIO sendEvent resp code:'. $resp->status());
            return $resp->status() === 200;
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    /** sync customer io user data
     * @param CustomerIoUser $user
     * @return array|null
     */
    public static function syncCustomerIoUser(CustomerIoUser $user): ?array
    {
        if (!self::getInstance()->enabled) return null;
        if (empty($user->getEmail())) {
            Log::warning(__CLASS__." no email, skipping sync cio user ".$user->getUserId());
            return null;
        }
        return self::getInstance()->identity($user->getUserId(), $user->toCioAttributes());
    }

    public static function changeAttribute(int $user_id, string $attribute, $value): void
    {
        try {
            /** @var CustomerIoUser $c_user */
            $c_user = CustomerIoUser::where('user_id', $user_id)->first();
            if ($c_user === null) {
                SyncCustomerIoJob::dispatch($user_id);
                return;
            }
            if ($c_user->getAttribute($attribute) === $value) return;
            $c_user->setAttribute($attribute, $value);
            $c_user->save();
            $c_user->syncData();
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    public static function syncData(int $user_id): void
    {
        try {
            /** @var CustomerIoUser $c_user */
            $c_user = CustomerIoUser::where('user_id', $user_id)->first();
            if ($c_user === null) {
                SyncCustomerIoJob::dispatch($user_id);
                return;
            }
            $c_user->syncData();
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public static function getInstance(): CustomerIo
    {
        if (self::$_instance === null) {
            self::$_instance = new CustomerIo();
        }

        return self::$_instance;
    }
}
