<?php

namespace App\Console\Commands;

use App\Helpers\Currency;
use App\Models\InvestTransaction;
use App\PayoutTransaction;
use Illuminate\Console\Command;

/**
 * @deprecated
 */
class EmulateTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emulateTransactions';

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
        $this->info('Transactions emulator started at '.date('d/m/Y H:i'));

        $targetDate = new \DateTime();
        $oneDay     = new \DateInterval('PT24H');
        $percents   = rand(5, 10);
        $minSum     = round(Currency::convert(200, Currency::RUB, Currency::USD), 2);
        $maxTries   = 30;

        $this->info('Min. sum: '.$minSum.' USD');

        // Fetch nearest sum from the invest transactions.
        $this->info('Looking for transactions...');
        $tries = 0;
        do {
            $targetDate->sub($oneDay);
            $sum = InvestTransaction::where('created_at', '>=', $targetDate->format('Y-m-d').' 00:00:00')
                ->where('created_at', '<=', $targetDate->format('Y-m-d').' 23:59:59')
                ->sum('amount');

            if ($sum === 0) {
                $this->info('There are no transactions on '.$targetDate->format('d/m/Y'));
            } else if ($sum < $minSum) {
                $this->info('Sum is too small ('.$sum.') on '.$targetDate->format('d/m/Y'));
            }

            $tries++;
        }while($sum < $minSum && $tries < $maxTries);

        if ($tries >= $maxTries) {
            $this->error('No transactions found (max tries reached).');
            $this->info('Done.');
            return '';
        }
        $this->info('Transactions found on '.$targetDate->format('d/m/Y'));
        $this->info('Sum: '.$sum.' USD');

        // Convert in rub.
        $sum = Currency::convert($sum, Currency::USD, Currency::RUB);
        $this->info('Converted: '.$sum.' RUB');

        // Distribute sums.
        $this->info('Distributing sum...');
        $sumToIncome = round($sum/2);
        $sumToOutcome = round($sumToIncome - ($sum * $percents / 100));
        $incomes = $this->distributeMoney($sumToIncome, ceil($sumToIncome/250), 50, true);
        $outcomes = $this->distributeMoney($sumToOutcome, ceil($sumToIncome/250/rand(3, 5)), 50);

        // Calculate final sum to income.
        $sumToIncome = 0;
        foreach ($incomes as $i) $sumToIncome += $i;

        // Calculate final sum to outcome.
        $sumToOutcome = 0;
        foreach ($outcomes as $i) $sumToOutcome += $i;

        $this->info('Distribution finished:');
        $this->info(count($incomes)." incomes ($sumToIncome RUB)");
        $this->info(count($outcomes)." outcomes ($sumToOutcome RUB)");
        $this->info('Delta: '.($sumToIncome - $sumToOutcome).' RUB ('.round(($sumToIncome - $sumToOutcome)/$sum * 100).'%)');

        // Create incoming transactions.
        $this->info('Creating incoming transactions...');
        foreach ($incomes as $s) {
            $this->createTransaction($s, PayoutTransaction::STATUS_INVEST_PAYMENT, 'Invest emulated payment');
        }

        // Create outgoing transactions.
        $this->info('Creating outgoing transactions...');
        foreach ($outcomes as $s) {
            $this->createTransaction($s, PayoutTransaction::STATUS_INVEST_PAYOUT, 'Invest emulated payout');
        }
        $this->info('Done.');
        return '';
    }

    /**
     * Distributes given sum to few random parts with some random offset.
     * The sum of resulting numbers will be equal with given one in the arguments.
     * @param float $sum       Sum to be distributed.
     * @param int $parts       Quantity of parts in the resulting array.
     * @param int $offset      Allowed offset in percents [0 - 100].
     * @param bool $toHundreds If true each part will be simplified to hundreds.
     * @return int[]
     */
    public function distributeMoney(float $sum, int $parts, int $offset = 25, bool $toHundreds = false):array
    {
        // If given sum too small - just return it.
        if ($sum < $parts || $sum < 200) return [$sum];

        $res = [];

        $partSize = round($sum/$parts);
        $partOffset = $partSize * $offset / 100;

        $tmpSum = 0;
        $number = 0;
        do {
            if ($number > 0) {
                $tmpSum += $number;
                $res []= $number;
            }
            $number = rand($partSize - $partOffset, $partSize + $partOffset);
            if ($toHundreds) $number = floor($number/100) * 100;

        } while($tmpSum + $number < $sum);

        $number = $toHundreds ? floor(($sum - $tmpSum)/100)*100 : $sum - $tmpSum;
        if ($number > 0) $res []= $number;
        return $res;
    }


    public function createTransaction(int $sum, int $status, string $comment)
    {
        $transaction = new PayoutTransaction();
        $transaction->user_id = 3; // Valera is here again!
        $transaction->hall_id = 237;
        $transaction->hall_credits = 0;
        $transaction->date = date('Y-m-d H:i:s');
        $transaction->sum = $sum;
        $transaction->operation = $comment;
        $transaction->status_id = $status;
        $transaction->type_payout_id = 'invest';
        $transaction->use_bonus = 0;
        $transaction->save();
    }
}
