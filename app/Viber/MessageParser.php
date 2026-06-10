<?php


namespace App\Viber;


use App\Viber\Commands\CommandBalance;
use App\Viber\Commands\CommandBonuses;
use App\Viber\Commands\CommandCancel;
use App\Viber\Commands\CommandConfirmLogin;
use App\Viber\Commands\CommandEvents;
use App\Viber\Commands\CommandGtktest;
use App\Viber\Commands\CommandInterface;
use App\Viber\Commands\CommandLogin;
use App\Viber\Commands\CommandPromocode;
use App\Viber\Commands\CommandResendEmail;
use App\Viber\Commands\CommandResendPhone;
use App\Viber\Commands\CommandStart;
use App\Viber\Models\ViberUser;
use Illuminate\Support\Facades\Log;

class MessageParser
{

    /** @var CommandInterface[] */
    protected array $commands = [];
    protected ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
//        $this->commands['bonuses'] = new CommandBonuses($bot);
//        $this->commands['balance'] = new CommandBalance($bot);
//        $this->commands['gtktest'] = new CommandGtktest($bot);
//        $this->commands['start'] = new CommandStart($bot);
//        $this->commands['login'] = new CommandLogin($bot);
//        $this->commands['events'] = new CommandEvents($bot);
//        $this->commands['promocode'] = new CommandPromocode($bot);
//        $this->commands['cancel'] = new CommandCancel($bot);
//        $this->commands['confirm_login'] = new CommandConfirmLogin($bot);
//        $this->commands['resend_email'] = new CommandResendEmail($bot);
//        $this->commands['resend_phone'] = new CommandResendPhone($bot);
    }

    /**
     * @param $name
     * @return CommandInterface
     * @throws \Exception
     */
    public function getCommand($name): CommandInterface
    {
        switch ($name) {
            case 'bonuses':
                return new CommandBonuses($this->bot);
            case 'balance':
                return new CommandBalance($this->bot);
            case 'gtktest':
                return new CommandGtktest($this->bot);
            case 'start':
                return new CommandStart($this->bot);
            case 'login':
                return new CommandLogin($this->bot);
            case 'events':
                return new CommandEvents($this->bot);
            case 'promocode':
                return new CommandPromocode($this->bot);
            case 'cancel':
                return new CommandCancel($this->bot);
            case 'confirm_login':
                return new CommandConfirmLogin($this->bot);
            case 'resend_email':
                return new CommandResendEmail($this->bot);
            case 'resend_phone':
                return new CommandResendPhone($this->bot);
        }

        throw new \Exception('Command not found');
    }

    /**
     * @param string $command
     * @param string $params
     * @param ViberUser $v_user
     * @param $update
     * @return mixed
     */
    protected function parseCommand(string $command, string $params, ViberUser $v_user, $update)
    {
        try {
            $cmd = $this->getCommand($command);
            return $cmd->process($params, $v_user, $update);
        } catch (\Exception $e) {
            Log::channel('viber')->error($e->getMessage());
            return $v_user->sendText('Команда не найдена');
        }
        return false;

//        if(!isset($this->commands[$command])){
//            return $v_user->sendText('Команда не найдена');
//        }
//        return $this->commands[$command]->process($params, $v_user, $update);
    }

    /**
     * @param ViberUser $v_user
     * @param $message_text
     * @param $update
     * @return bool
     */
    public function parseMessage(ViberUser $v_user, $message_text, $update): bool
    {
        //check for command
        if (preg_match('#^/(\S+)\s?(.*)$#', $message_text, $match)) {
            $this->parseCommand($match[1], $match[2], $v_user, $update);
            return true;
        }
        return false;
    }
}
