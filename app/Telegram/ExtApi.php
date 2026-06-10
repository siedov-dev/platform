<?php


namespace App\Telegram;


use App\Telegram\Models\TelegramStat;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Message as MessageObject;
use Telegram\Bot\TelegramResponse;

class ExtApi extends Api
{

    private string $_resp = "";

    public function sendApiMessage(array $params): ?MessageObject
    {
        try {
            $log = new TgBotApiLog();
            $log->setSendParams(json_encode($params, JSON_UNESCAPED_UNICODE));

            $response = $this->sendMessage($params);
            TelegramStat::incMessages();
            $this->_resp = json_encode($response, JSON_UNESCAPED_UNICODE);

            $log->setResponse($this->_resp);
            $log->save();
            return $response;
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            Log::channel('telegram')->error($e->getLine());
            Log::channel('telegram')->error($e->getFile());
            Log::channel('telegram')->error($e->getTraceAsString());
            TelegramStat::incErrors();
            if (preg_match('#blocked by the user#', $e->getMessage())) {
                TelegramStat::incBlocked();
            }
            if (preg_match('#user is deactivated#', $e->getMessage())) {
                TelegramStat::incBlocked();
            }
            $this->_resp = json_encode($e->getMessage());

            $log->setResponse($this->_resp);
            $log->save();
        }
        return null;
    }

    /**
     * Send Photos.
     *
     * <code>
     * $params = [
     *   'chat_id'             => '',
     *   'photo'               => '',
     *   'caption'             => '',
     *   'reply_to_message_id' => '',
     *   'reply_markup'        => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendphoto
     *
     * @param array $params
     *
     * @return MessageObject
     */
    public function sendApiPhoto(array $params): ?MessageObject
    {
        try {
            $log = new TgBotApiLog();
            $log->setSendParams(json_encode($params, JSON_UNESCAPED_UNICODE));

            $response = $this->uploadFile('sendPhoto', $params, 'photo');
            $this->_resp = json_encode($response, JSON_UNESCAPED_UNICODE);

            $log->setResponse($this->_resp);
            $log->save();
            return new MessageObject($response->getDecodedBody());
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            TelegramStat::incErrors();
            if (preg_match('#blocked by the user#', $e->getMessage())) {
                TelegramStat::incBlocked();
            }
            if (preg_match('#user is deactivated#', $e->getMessage())) {
                TelegramStat::incBlocked();
            }
            $this->_resp = json_encode($e->getMessage());

            $log->setResponse($this->_resp);
            $log->save();
        }
        return null;
    }

    public function getResp(): string
    {
        return $this->_resp;
    }

}
