<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Log;

class OneSignalApi implements NotifierApiInterface
{
    const API_GATEWAY = 'https://onesignal.com/api/v1/notifications';

    const SEGMENT_SUBSCRIBED = 'Subscribed Users';

    private $_appId;
    private $_key;

    public function __construct()
    {
        $this->_appId = config('notifications.appId', '');
        $this->_key = config('notifications.key', '');
    }

    public function send(Notification $ntf): bool
    {
        $buttonCounter = 1;


        $buttons = [];
        foreach ($ntf->getButtons() as $button) {
            $d = [
                'id'   => 'button-' . $buttonCounter++,
                'text' => $button->getText(),
                'url'  => $button->getUrl(),
            ];
            if ($button->getIconUrl() !== null) {
                $d['icon'] = $button->getIconUrl();
            }
            $buttons[] = $d;
        }

        $fields = [
            'app_id' => $this->_appId,
            'included_segments' => [self::SEGMENT_SUBSCRIBED],
            'contents' => $ntf->getContentTranslations(),
            'web_buttons' => $buttons
        ];

        if (!$ntf->isTitleEmpty()) {
            $fields['headings'] = $ntf->getTitleTranslations();
        }

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, static::API_GATEWAY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $this->_key
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        try {
            $res = json_decode($response, true);
            if (array_key_exists('errors', $res)) {
                Log::error('Error on parsing notification creating response:' . PHP_EOL . $response);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error on parsing notification creating response:' . $e->getMessage());
            Log::error('Response: ' . var_export($response, true));
            return false;
        }

        return is_array($res);
    }
}
