<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PaymentsAPI\PayCraft;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\XPayPro\XPayPro;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XPayProPayout extends Payout implements AutoPayoutSystem
{
    private XPayPro $api;

    private array $ncpk = [
        100000000009 => "QIWI Кошелек (КИВИ Банк)",
        100000000095 => "АБ РОССИЯ",
        100000000258 => "АИКБ Енисейский объединенный банк",
        100000000235 => "АКБ Держава",
        100000000167 => "АКБ ЕВРОФИНАНС МОСНАРБАНК",
        100000000245 => "АКБ Ланта Банк",
        100000000200 => "АКБ СЛАВИЯ",
        100000000175 => "АКБ Тендер Банк",
        100000000107 => "АКИБАНК",
        100000000047 => "Абсолют Банк",
        100000000253 => "Авто Финанс Банк",
        100000000130 => "Автоградбанк",
        100000000181 => "Автоторгбанк",
        100000000108 => "Азиатско-Тихоокеанский Банк",
        100000000006 => "Ак Барс Банк",
        100000000113 => "Алеф-Банк",
        100000000080 => "Алмазэргиэнбанк",
        100000000008 => "Альфа Банк",
        100000000234 => "БАНК МОСКВА СИТИ",
        100000000124 => "БАНК ОРЕНБУРГ",
        100000000219 => "БАНК СГБ",
        100000000091 => "БАНК СНГБ",
        100000000026 => "БАНК УРАЛСИБ",
        100000000133 => "ББР Банк",
        100000000041 => "БКС Банк",
        100000000028 => "Банк АВАНГАРД",
        100000000211 => "Банк АЛЕКСАНДРОВСКИЙ",
        100000000154 => "Банк Аверс",
        100000000102 => "Банк Агророс",
        100000000135 => "Банк Акцепт",
        100000000260 => "Банк БЖФ",
        100000000227 => "Банк БКФ",
        100000000049 => "Банк ВБРР",
        100000000153 => "Банк Венец",
        100000000257 => "Банк Вологжанин",
        100000000082 => "Банк ДОМ.РФ",
        100000000090 => "Банк Екатеринбург",
        100000000045 => "Банк ЗЕНИТ",
        100000000205 => "Банк Заречье",
        100000000236 => "Банк ИПБ",
        100000000158 => "Банк ИТУРУП",
        100000000170 => "Банк Интеза",
        100000000201 => "Банк Кремлевский",
        100000000052 => "Банк Левобережный",
        100000000192 => "Банк МБА МОСКВА",
        100000000243 => "Банк Национальный стандарт",
        100000000182 => "Банк Объединенный капитал",
        100000000087 => "Банк ПСКБ",
        100000000226 => "Банк Приморье",
        100000000187 => "Банк РЕСО Кредит",
        100000000172 => "Банк Развитие-Столица",
        100000000014 => "Банк Русский Стандарт",
        100000000278 => "Банк СИАБ",
        100000000029 => "Банк Санкт-Петербург",
        100000000126 => "Банк Саратов",
        100000000003 => "Банк Синара",
        100000000163 => "Банк Снежинский",
        100000000096 => "Банк Уралфинанс",
        100000000040 => "Банк ФИНАМ",
        100000000015 => "Банк ФК Открытие",
        100000000216 => "Банк Финсервис",
        100000000231 => "Банк ЦентроКредит",
        100000000092 => "БыстроБанк",
        100000000058 => "ВЛАДБИЗНЕСБАНК",
        100000000248 => "ВНЕШФИНБАНК",
        100000000005 => "ВТБ",
        100000000215 => "ВУЗ-банк",
        100000000112 => "ГАРАНТ-ИНВЕСТ БАНК",
        100000000037 => "ГЕНБАНК",
        100000000125 => "ГОРБАНК",
        100000000149 => "ГУТА-БАНК",
        100000000001 => "Газпромбанк",
        100000000183 => "Газтрансбанк",
        100000000043 => "Газэнергобанк",
        100000000083 => "Дальневосточный банк",
        100000000070 => "Датабанк",
        100000000213 => "Джей энд Ти Банк (АО)",
        100000000250 => "Драйв Клик Банк",
        100000000066 => "Земский банк",
        100000000122 => "ИК Банк",
        100000000239 => "ИС Банк",
        100000000199 => "ИШБАНК",
        100000000196 => "Инбанк",
        100000000078 => "Ингосстрах Банк",
        100000000098 => "КБ РостФинанс",
        100000000118 => "КБ АГРОПРОМКРЕДИТ",
        100000000129 => "КБ АРЕСБАНК",
        100000000270 => "КБ Долинск",
        100000000212 => "КБ Крокус Банк",
        100000000161 => "КБ ЛОКО-Банк",
        100000000099 => "КБ Модульбанк",
        100000000110 => "КБ Москоммерцбанк",
        100000000103 => "КБ Пойдём",
        100000000121 => "КБ Солидарность",
        100000000193 => "КБ Стройлесбанк",
        100000000056 => "КБ Хлынов",
        100000000139 => "КБ ЭНЕРГОТРАНСБАНК",
        100000000191 => "КБЭР Банк Казани",
        100000000146 => "КОШЕЛЕВ-БАНК",
        100000000027 => "Кредит Европа Банк (Россия)",
        100000000064 => "Кредит Урал Банк",
        100000000050 => "Кубань Кредит",
        100000000180 => "Кубаньторгбанк",
        100000000195 => "Кузнецкбизнесбанк",
        100000000140 => "МБ Банк",
        100000000203 => "МЕЖДУНАРОДНЫЙ ФИНАНСОВЫЙ КЛУБ",
        100000000136 => "МЕТКОМБАНК",
        100000000025 => "МКБ (Московский кредитный банк)",
        100000000171 => "МОРСКОЙ БАНК",
        100000000176 => "МОСКОМБАНК",
        100000000229 => "МС Банк Рус",
        100000000246 => "МСП Банк",
        100000000017 => "МТС-Банк",
        100000000046 => "Металлинвестбанк",
        100000000169 => "Мир Привилегий (МП Банк)",
        100000000134 => "НБД-Банк",
        100000000115 => "НИКО-БАНК",
        100000000233 => "НК Банк",
        100000000177 => "НОВИКОМБАНК",
        100000000062 => "НОКССБАНК",
        100000000071 => "НС Банк",
        100000000185 => "Нацинвестпромбанк",
        100000000222 => "Новобанк",
        100000000067 => "Новый век",
        100000000202 => "Норвик Банк",
        100000000018 => "ОТП Банк",
        100000000273 => "Озон Банк (Ozon)",
        100000000174 => "Первый Инвестиционный Банк",
        100000000016 => "Почта Банк",
        100000000228 => "Прио-Внешторгбанк",
        100000000117 => "ПроБанк",
        100000000010 => "Промсвязьбанк",
        100000000011 => "РНКБ Банк",
        100000000194 => "РУСНАРБАНК",
        100000000007 => "Райффайзен Банк",
        100000000232 => "Реалист Банк",
        100000000032 => "Ренессанс Кредит",
        100000000084 => "РосДорБанк",
        100000000012 => "Росбанк",
        100000000020 => "Россельхозбанк",
        100000000165 => "Русьуниверсалбанк",
        100000000069 => "СДМ-Банк",
        100000000166 => "СИБСОЦБАНК",
        100000000148 => "СИНКО-БАНК",
        100000000088 => "СКБ Примсоцбанк",
        100000000036 => "СМП Банк",
        100000000223 => "СОЦИУМ БАНК",
        100000000111 => "Сбербанк",
        100000000208 => "Северный Народный Банк",
        100000000128 => "Ситибанк",
        100000000013 => "Совкомбанк",
        100000000230 => "Солид Банк",
        100000000189 => "ТАТСОЦБАНК",
        100000000065 => "ТОЧКА (ФК ОТКРЫТИЕ)",
        100000000034 => "ТРАНСКАПИТАЛБАНК",
        100000000173 => "Таврический Банк",
        100000000144 => "Тимер Банк",
        100000000004 => "Тинькофф Банк",
        100000000138 => "Тойота Банк",
        100000000152 => "Тольяттихимбанк",
        100000000206 => "Томскпромстройбанк",
        100000000284 => "Точка Банк",
        100000000197 => "Трансстройбанк",
        100000000031 => "УБРиР",
        100000000225 => "УКБ Белгородсоцбанк",
        100000000142 => "УРАЛПРОМБАНК",
        100000000093 => "Углеметбанк",
        100000000151 => "Урал ФД",
        100000000217 => "ФОРА-БАНК",
        100000000265 => "ФФИН Банк (Цифра банк)",
        100000000081 => "Форштадт",
        100000000272 => "Хайс",
        100000000127 => "Хакасский муниципальный банк",
        100000000024 => "Хоум Кредит Банк (Хоум Банк)",
        100000000059 => "Центр-инвест",
        100000000106 => "ЧЕЛИНДБАНК",
        100000000094 => "ЧЕЛЯБИНВЕСТБАНК",
        100000000086 => "ЭЛПЛАТ",
        100000000044 => "Экспобанк",
        100000000159 => "Энергобанк",
        100000000105 => "Эс-Би-Ай Банк",
        100000000160 => "ЮГ-Инвестбанк",
        100000000022 => "ЮМани",
        100000000042 => "ЮНИСТРИМ БАНК",
        100000000030 => "ЮниКредит Банк",
        100000000150 => "Яндекс Банк",
        100000000247 => "банк Раунд",
        100000000266 => "банк Элита",
    ];

    public function __construct()
    {
        $this->api = new XPayPro();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->comentar = 'init';

            if ($applicationForPayment->save()) {

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $requsites = json_decode($applicationForPayment->requisites, true);

                $response = $this->api->createP2pWithdraw(
                    client_id: $applicationForPayment->user_id,
                    amount: $applicationForPayment->money,
                    card: $applicationForPayment->card_number?? '',
                    method: $method,
                    requisites: $requsites,
                    currency_name: Wallet::currencyName($applicationForPayment->currency),
                );

                Log::channel('payout')->info(XPayProPayout::class . ' payout result: ', (array)$response);

                if (!empty($response) && !empty($response['tx']['tx_id'])) {
                    $applicationForPayment->comentar = 'created';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['tx']['tx_id'];
                } else {
                    $applicationForPayment->comentar = $response['message'] ?? 'unknown error';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->getTransaction($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

                if (empty($result['tx'])) {
//                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message'] ?? 'Ошибка запроса';
                    $applicationForPayment->save();

                } elseif ($result['tx']['tx_status'] === 'TX_CANCELLED' || $result['tx']['tx_status'] === 'TX_EXPIRED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['tx']['tx_status'];
                    $applicationForPayment->save();

                } elseif ($result['tx']['tx_status'] === 'TX_SUCCESS' || $result['tx']['tx_status'] === 'TX_RECALCULATED') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['tx']['out_amount'] ?? 0;
                    $applicationForPayment->save();
                    $fee = 0;

                    if (!empty($result['tx']['fee_pct'])) {
                        $fee = $applicationForPayment->suma * $result['tx']['fee_pct'] / 100;
                    }

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата XPayPro',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'outer_id not set';
                $applicationForPayment->save();
            }

        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'XPayPro.BANK_CARD') {
            return view('pay.requisites.payout.xpaypro.sber', ['method' => $method,])->render();
        }
        if ($method === 'XPayPro.SBP') {
            return view('pay.requisites.payout.xpaypro.sbp', ['method' => $method, 'ncpk' => $this->ncpk])->render();
        }
        return parent::getFormRequisites($method, $pay_method);
    }

}
