<?php


namespace App\Telegram;


use App\Telegram\Commands\CommandBalance;
use App\Telegram\Commands\CommandBonuses;
use App\Telegram\Commands\CommandEvents;
use App\Telegram\Commands\CommandGetBonus;
use App\Telegram\Commands\CommandGtktest;
use App\Telegram\Commands\CommandInterface;
use App\Telegram\Commands\CommandLogin;
use App\Telegram\Commands\CommandManager;
use App\Telegram\Commands\CommandManagerBonus;
use App\Telegram\Commands\CommandMirrors;
use App\Telegram\Commands\CommandPromocode;
use App\Telegram\Commands\CommandStart;
use App\Telegram\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class MessageParser
{

    /** @var CommandInterface[] */
    protected array $commands = [];
    protected TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
        $this->commands['bonuses'] = new CommandBonuses($bot);
        $this->commands['balance'] = new CommandBalance($bot);
        $this->commands['gtktest'] = new CommandGtktest($bot);
        $this->commands['start'] = new CommandStart($bot);
        $this->commands['login'] = new CommandLogin($bot);
        $this->commands['events'] = new CommandEvents($bot);
        $this->commands['getbonus'] = new CommandGetBonus($bot);
        $this->commands['promocode'] = new CommandPromocode($bot);
        $this->commands['manager'] = new CommandManager($bot);
        $this->commands['manbonus'] = new CommandManagerBonus($bot);
        $this->commands['mirrors'] = new CommandMirrors($bot);
    }

    protected function execCommand(string $command, string $params, User $user, TelegramUser $t_user, Message $message)
    {
        if (!isset($this->commands[$command])) {
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.command_not_found'),
//            'reply_to_message_id' => $message->getMessageId(),
            ]);
        }
        return $this->commands[$command]->process($params, $user, $t_user, $message);
    }

    public function parseMessage(User $user, TelegramUser $t_user, Message $message): bool
    {
        //check for command
        if (preg_match('#^/([0-9a-zA-Z]+) ?(.*)$#', $message->getText(), $match)) {
            $this->execCommand($match[1], $match[2], $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_connect_acc'), $message->getText(), $match)) {
            $this->commandLogin('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_balance'), $message->getText(), $match)) {
            $this->commandBalance('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_bonuses'), $message->getText(), $match)) {
            $this->commandBonuses('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_activities'), $message->getText(), $match)) {
            $this->commandEvents('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_promocode'), $message->getText(), $match)) {
            $this->commandPromocode('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_bezdep'), $message->getText(), $match)) {
            $this->commands['getbonus']->process('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_manager_bonus'), $message->getText(), $match)) {
            $this->commands['manbonus']->process('', $user, $t_user, $message);
            return true;
        }
        if (preg_match(__('telegram.ptn_mirrors'), $message->getText(), $match)) {
            $this->commands['mirrors']->process('', $user, $t_user, $message);
            return true;
        }
        Log::channel('telegram')->info(__('telegram.ptn_personal_manager'));
        if (preg_match(__('telegram.ptn_personal_manager'), $message->getText(), $match)) {
            $this->commandManager('', $user, $t_user, $message);
            return true;
        }

        return false;
    }

    public function commandLogin($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['login']->process($params, $user, $t_user, $message);
    }

    public function commandBalance($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['balance']->process($params, $user, $t_user, $message);
    }

    public function commandBonuses($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['bonuses']->process($params, $user, $t_user, $message);
    }

    public function commandEvents($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['events']->process($params, $user, $t_user, $message);
    }

    public function commandPromocode($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['promocode']->process($params, $user, $t_user, $message);
    }

    public function commandManager($params, User $user, TelegramUser $t_user, Message $message)
    {
        return $this->commands['manager']->process($params, $user, $t_user, $message);
    }
}
