<?php

namespace App\Console\Commands;

use App\Notifications\Notification;
use App\Notifications\NotificationButton;
use App\Notifications\Notifier;
use Illuminate\Console\Command;

class testNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ntf = new Notification();

        $ntf->setTitle('ru', 'Кракен снова на связи!');
        $ntf->setContent('ru', 'У нас для вас есть новые бонусы, а также турнир.');

        $button = new NotificationButton('Получить', 'https://krakencassino.com/bonuses');
        $ntf->addButton($button);

        Notifier::send($ntf);
    }
}
