<?php


namespace App\Viber;


use App\Viber\Models\ViberStat;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ViberApi
{
    protected string $token = "4e90d30833e7df18-62c7dac44fe261d5-2beec49c15f89dea";
    protected string $uri = "viber://pa?chatURI=krakencassinocom";

    public static function setWebHook()
    {
        $client = new Client(
            [
                'base_uri' => 'https://chatapi.viber.com/pa/',
                'timeout' => 5.0,
            ]
        );

        $headers = [
            'X-Viber-Auth-Token' => '4e90d30833e7df18-62c7dac44fe261d5-2beec49c15f89dea',
        ];

        $post_data = [
            "url" => "https://krakencassino.com/api/viber",
            "event_types" => [
                "delivered",
                "seen",
                "failed",
                "subscribed",
                "unsubscribed",
                "conversation_started"
            ],
            "send_name" => true,
            "send_photo" => false,
        ];
        $req = new \GuzzleHttp\Psr7\Request('POST', 'https://chatapi.viber.com/pa/set_webhook', $headers, json_encode($post_data));

        $r = $client->send($req);

        Log::info($r->getStatusCode());
        Log::info($r->getBody());
    }

    public static function removeWebHook()
    {
        $client = new Client(
            [
                'base_uri' => 'https://chatapi.viber.com/pa/',
                'timeout' => 5.0,
            ]
        );

        $headers = [
            'X-Viber-Auth-Token' => '4e90d30833e7df18-62c7dac44fe261d5-2beec49c15f89dea',
        ];

        $post_data = [
            "url" => "",
        ];
        $req = new \GuzzleHttp\Psr7\Request('POST', 'https://chatapi.viber.com/pa/set_webhook', $headers, json_encode($post_data));

        $r = $client->send($req);

        Log::info($r->getStatusCode());
        Log::info($r->getBody());
    }

    /**
     * @param string $viber_uid
     * @param $message
     * @param null $keyboard
     * @param string|null $tracking_data
     * @return void
     */
    public function sendMessage(string $viber_uid, $message, $keyboard = null, string $tracking_data = null): void
    {
        try {
            $data = [
                "receiver" => $viber_uid,
                "min_api_version" => 3,// phone sharing starts from
//            "sender" => [
//                "name" => "John McClane",
//                "avatar" => "http://avatar.example.com",
//            ],
                "type" => "text",
                "text" => $message,
            ];

            if (!empty($keyboard)) {
                $data['keyboard'] = $keyboard;
            }

            if (!empty($tracking_data)) {
                $data['tracking_data'] = $tracking_data;
            }
            $this->sendQuery('send_message', $data);
            ViberStat::incMessages();

        } catch (\Exception $e) {
            Log::channel('viber')->error($e->getMessage());
            ViberStat::incErrors();
        }
    }

    public function sendImage(string $viber_uid, $img_url, $description = "", $thumbnail = null, $keyboard = null, string $tracking_data = null)
    {
        try {
            $data = [
                "receiver" => $viber_uid,
                "min_api_version" => 3,
                "type" => "picture",
                "text" => $description,
                "media" => $img_url,
            ];

            if (!empty($keyboard)) {
                $data['keyboard'] = $keyboard;
            }

            if (!empty($thumbnail)) {
                $data['thumbnail'] = $thumbnail;
            }

            if (!empty($tracking_data)) {
                $data['tracking_data'] = $tracking_data;
            }
            $this->sendQuery('send_message', $data);
            ViberStat::incImages();

        } catch (\Exception $e) {
            Log::channel('viber')->error($e->getMessage());
        }
    }

    protected function sendQuery(string $method, array $data)
    {
        $client = new Client(
            [
                'base_uri' => 'https://chatapi.viber.com/pa/',
                'timeout' => 5.0,
            ]
        );

        $headers = [
            'X-Viber-Auth-Token' => $this->token,
        ];

        Log::channel('viber')->info('method:'.$method);
        Log::channel('viber')->info('data:'.json_encode($data, JSON_UNESCAPED_SLASHES));

        $req = new \GuzzleHttp\Psr7\Request($method, 'https://chatapi.viber.com/pa/' . $method, $headers, json_encode($data, JSON_UNESCAPED_SLASHES));

        $resp = $client->send($req);
        Log::channel('viber')->info($resp->getBody());
        return $resp;
    }
}
