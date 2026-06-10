<?php

namespace App\CustomerIo;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusPatterns;
use App\Models\UserGameStat;
use App\MUser;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $reg_date
 * @property ?string $birthday
 * @property ?Carbon $first_dep_date
 * @property ?Carbon $last_dep_date
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property bool $email_verified
 * @property string $phone
 * @property bool $phone_verified
 * @property int $rank
 * @property string $partner
 * @property int $currency
 * @property string $lang
 * @property string $country
 * @property float $dep_sum
 * @property float $dep_avg
 * @property int $dep_cnt
 * @property bool $blocked
 * @property float $dep_sum_7d
 * @property float $dep_sum_30d
 * @property int $b1
 * @property int $b1fs
 * @property int $b2
 * @property int $b2fs
 * @property int $b3
 * @property int $b3fs
 * @property int $b4
 * @property int $b5
 * @property int $wb
 * @property int $wb_state
 * @property ?string $timezone
 * @property ?string $top_game_id
 * @property ?string $top_game_name
 * @property ?string $top_game_provider
 * @property ?string $top_game_system
 * @property ?string $top_provider
 * @property ?string $top_provider_system
 * @property ?int $tg_id
 * @property ?string $lang_tg
 */
class CustomerIoUser extends Model
{
    use HasFactory;

    protected $dates = [
        'reg_date',
        'first_dep_date',
        'last_dep_date',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getRegDate(): Carbon
    {
        return $this->reg_date;
    }

    public function setRegDate(Carbon $reg_date): void
    {
        $this->reg_date = $reg_date;
    }

    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    public function setBirthday(?Carbon $birthday): void
    {
        $this->birthday = $birthday->format('Y-m-d');
    }

    public function getFirstDepDate(): ?Carbon
    {
        return $this->first_dep_date;
    }

    public function setFirstDepDate(?Carbon $first_dep_date): void
    {
        $this->first_dep_date = $first_dep_date;
    }

    public function getLastDepDate(): ?Carbon
    {
        return $this->last_dep_date;
    }

    public function setLastDepDate(?Carbon $last_dep_date): void
    {
        $this->last_dep_date = $last_dep_date;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function getPartner(): string
    {
        return $this->partner;
    }

    public function setPartner(string $partner): void
    {
        $this->partner = $partner;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function setCurrency(int $currency): void
    {
        $this->currency = $currency;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getCountry(): string
    {
        return $this->country?? "";
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getDepSum(): float
    {
        return $this->dep_sum;
    }

    public function setDepSum(float $dep_sum): void
    {
        $this->dep_sum = $dep_sum;
    }

    public function getDepAvg(): float
    {
        return $this->dep_avg;
    }

    public function setDepAvg(float $dep_avg): void
    {
        $this->dep_avg = $dep_avg;
    }

    public function getDepCnt(): int
    {
        return $this->dep_cnt;
    }

    public function setDepCnt(int $dep_cnt): void
    {
        $this->dep_cnt = $dep_cnt;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): void
    {
        $this->blocked = $blocked;
    }

    public function getDepSum7d(): float
    {
        return $this->dep_sum_7d;
    }

    public function setDepSum7d(float $dep_sum_7d): void
    {
        $this->dep_sum_7d = $dep_sum_7d;
    }

    public function getDepSum30d(): float
    {
        return $this->dep_sum_30d;
    }

    public function setDepSum30d(float $dep_sum_30d): void
    {
        $this->dep_sum_30d = $dep_sum_30d;
    }

    public function getB1(): int
    {
        return $this->b1?? 0;
    }

    public function setB1(int $b1): void
    {
        $this->b1 = $b1;
    }

    public function getB1fs(): int
    {
        return $this->b1fs?? 0;
    }

    public function setB1fs(int $b1fs): void
    {
        $this->b1fs = $b1fs;
    }

    public function getB2(): int
    {
        return $this->b2?? 0;
    }

    public function setB2(int $b2): void
    {
        $this->b2 = $b2;
    }

    public function getB2fs(): int
    {
        return $this->b2fs?? 0;
    }

    public function setB2fs(int $b2fs): void
    {
        $this->b2fs = $b2fs;
    }

    public function getB3(): int
    {
        return $this->b3?? 0;
    }

    public function setB3(int $b3): void
    {
        $this->b3 = $b3;
    }

    public function getB3fs(): int
    {
        return $this->b3fs?? 0;
    }

    public function setB3fs(int $b3fs): void
    {
        $this->b3fs = $b3fs;
    }

    public function getB4(): int
    {
        return $this->b4?? 0;
    }

    public function setB4(int $b4): void
    {
        $this->b4 = $b4;
    }

    public function getB5(): int
    {
        return $this->b5?? 0;
    }

    public function setB5(int $b5): void
    {
        $this->b5 = $b5;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified;
    }

    public function setEmailVerified(bool $email_verified): void
    {
        $this->email_verified = $email_verified;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified;
    }

    public function setPhoneVerified(bool $phone_verified): void
    {
        $this->phone_verified = $phone_verified;
    }

    public function getWb(): int
    {
        return $this->wb?? 0;
    }

    public function setWb(int $wb): void
    {
        $this->wb = $wb;
    }

    public function getWbState(): int
    {
        return $this->wb_state?? 0;
    }

    public function setWbState(int $wb_state): void
    {
        $this->wb_state = $wb_state;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function getTopGameId(): ?string
    {
        return $this->top_game_id;
    }

    public function setTopGameId(?string $top_game_id): void
    {
        $this->top_game_id = $top_game_id;
    }

    public function getTopGameName(): ?string
    {
        return $this->top_game_name;
    }

    public function setTopGameName(?string $top_game_name): void
    {
        $this->top_game_name = $top_game_name;
    }

    public function getTopGameProvider(): ?string
    {
        return $this->top_game_provider;
    }

    public function setTopGameProvider(?string $top_game_provider): void
    {
        $this->top_game_provider = $top_game_provider;
    }

    public function getTopGameSystem(): ?string
    {
        return $this->top_game_system;
    }

    public function setTopGameSystem(?string $top_game_system): void
    {
        $this->top_game_system = $top_game_system;
    }

    public function getTopProvider(): ?string
    {
        return $this->top_provider;
    }

    public function setTopProvider(?string $top_provider): void
    {
        $this->top_provider = $top_provider;
    }

    public function getTopProviderSystem(): ?string
    {
        return $this->top_provider_system;
    }

    public function setTopProviderSystem(?string $top_provider_system): void
    {
        $this->top_provider_system = $top_provider_system;
    }

    public function getTgId(): ?int
    {
        return $this->tg_id;
    }

    public function setTgId(?int $tg_id): void
    {
        $this->tg_id = $tg_id;
    }

    public function getLangTg(): ?string
    {
        return $this->lang_tg;
    }

    public function setLangTg(?string $lang_tg): void
    {
        $this->lang_tg = $lang_tg;
    }

    public function toCioAttributes(): array
    {
        $attributes = [];
        $attributes['user_id'] = $this->getUserId();
        $attributes['created_at'] = $this->getRegDate()->unix();
        $attributes['reg_date'] = $this->getRegDate()->unix();
        $attributes['birthday'] = $this->getBirthday()?? "";
        $attributes['first_dep_date'] = $this->getFirstDepDate()?->unix();
        $attributes['last_dep_date'] = $this->getLastDepDate()?->unix();
        $attributes['first_name'] = $this->getFirstName();
        $attributes['last_name'] = $this->getLastName();
        $attributes['email'] = $this->getEmail();
        $attributes['email_verified'] = $this->isEmailVerified();
        $attributes['phone_verified'] = $this->isPhoneVerified();
        $attributes['phone'] = $this->getPhone();
        $attributes['rank'] = $this->getRank();
        $attributes['partner'] = $this->getPartner();
        $attributes['currency'] = Wallet::currencyName($this->getCurrency());
        $attributes['lang'] = $this->getLang();
        $attributes['country'] = $this->getCountry();
        $attributes['dep_sum'] = $this->getDepSum();
        $attributes['dep_cnt'] = $this->getDepCnt();
        if ($this->getDepCnt() > 0) {
            $attributes['dep_avg'] = $this->getDepSum() / $this->getDepCnt();
        } else {
            $attributes['dep_avg'] = 0;
        }
        $attributes['blocked'] = $this->isBlocked();
        $attributes['dep_sum_7d'] = $this->getDepSum7d();
        $attributes['dep_sum_30d'] = $this->getDepSum30d();
        $attributes['b1'] = $this->getB1();
        $attributes['b1fs'] = $this->getB1fs();
        $attributes['b2'] = $this->getB2();
        $attributes['b2fs'] = $this->getB2fs();
        $attributes['b3'] = $this->getB3();
        $attributes['b3fs'] = $this->getB2fs();
        $attributes['b4'] = $this->getB4();
        $attributes['b5'] = $this->getB5();
        $attributes['wb'] = $this->getWb();
        $attributes['wb_state'] = $this->getWbState();
        $attributes['timezome'] = $this->getTimezone();
        if ($attributes['lang'] == 'ua') $attributes['lang'] = 'uk';
        if ($attributes['lang'] == 'kz') $attributes['lang'] = 'kk';

        $attributes['top_game_id'] = $this->getTopGameId()?? "";
        $attributes['top_game_name'] = $this->getTopGameName()?? "";
        $attributes['top_game_provider'] = $this->getTopGameProvider()?? "";
        $attributes['top_game_system'] = $this->getTopGameSystem()?? "";
        $attributes['top_provider'] = $this->getTopProvider()?? "";
        $attributes['top_provider_system'] = $this->getTopProviderSystem()?? "";
        $attributes['chat_id'] = $this->getTgId()?? "";
        $attributes['lang_tg_bot'] = $this->getLangTg()?? "";

        /** @var MUser $user */
        $user = MUser::find($this->getUserId());
        if ($user) {
            $attributes['slc'] = $user->getSlcBalance();
        }

        return $attributes;
    }

    public static function updateData(int $user_id, array $data): CustomerIoUser
    {
        $d = $data;
        $io_user = self::where('user_id', $user_id)->first();
        if ($io_user === null) {
            $io_user = new CustomerIoUser();
        }
        $io_user->setUserId($user_id);
        $io_user->setCurrency($d['curr_id']);
        $io_user->setEmail($d['email']?? '');
        $io_user->setLang($d['lang']?? '');
        $io_user->setRegDate(Carbon::parse($d['reg_date']));
        $io_user->setPhone($d['phone']?? '');
        $io_user->setRank($d['rank']?? 0);
        if (!empty($d['first_dep_date'])) $io_user->setFirstDepDate(Carbon::parse($d['first_dep_date']));
        if (!empty($d['last_dep_date'])) $io_user->setLastDepDate(Carbon::parse($d['last_dep_date']));
        $io_user->setDepSum($d['dep_sum']?? 0);
        $io_user->setDepCnt($d['dep_cnt']?? 0);
        $io_user->setDepSum7d($d['dep_sum_7d']?? 0);
        $io_user->setDepSum30d($d['dep_sum_30d']?? 0);
        $io_user->setFirstName($d['first_name']?? '');
        $io_user->setLastName($d['last_name']?? '');
        $io_user->setTimezone($d['timezone']?? '');
        $io_user->setTgId($d['tg_id']?? 0);
        $io_user->setLangTg($d['lang_tg']?? '');

        if (!empty($d['birthday'] && $d['birthday'] !== '0000-00-00')) {
            try {

                $birthday = Carbon::createFromFormat('Y-m-d', $d['birthday']);
                $io_user->setBirthday($birthday);
            } catch (\Exception $e) {
                Hlog::logException($e, 'retention');
            }
        }
        $io_user->setPartner($d['partner']?? '');
        $io_user->setEmailVerified(!empty($d['email']) && empty($d['email_token']));
        $io_user->setPhoneVerified(!empty($d['phone']) && empty($d['phone_token']));
        $io_user->setBlocked($d['blocked']?? false);
        if (isset($d['b1'])) {
            if ($d['b1'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB1(2); //active
            } elseif(in_array($d['b1'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
                ])) {
                $io_user->setB1(1); //got but not active
            } else {
                $io_user->setB1(3); //done
            }
        }
        if (isset($d['b1fs'])) {
            if ($d['b1fs'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB1fs(2); //active
            } elseif(in_array($d['b1fs'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB1fs(1); //got but not active
            } else {
                $io_user->setB1fs(3); //done
            }
        }
        if (isset($d['b2'])) {
            if ($d['b2'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB2(2); //active
            } elseif(in_array($d['b2'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB2(1); //got but not active
            } else {
                $io_user->setB2(3); //done
            }
        }
        if (isset($d['b2fs'])) {
            if ($d['b2fs'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB2fs(2); //active
            } elseif(in_array($d['b2fs'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB2fs(1); //got but not active
            } else {
                $io_user->setB2fs(3); //done
            }
        }
        if (isset($d['b3'])) {
            if ($d['b3'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB3(2); //active
            } elseif(in_array($d['b3'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB3(1); //got but not active
            } else {
                $io_user->setB3(3); //done
            }
        }
        if (isset($d['b3fs'])) {
            if ($d['b3fs'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB3fs(2); //active
            } elseif(in_array($d['b3fs'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB3fs(1); //got but not active
            } else {
                $io_user->setB3fs(3); //done
            }
        }
        if (isset($d['b4'])) {
            if ($d['b4'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB4(2); //active
            } elseif(in_array($d['b4'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB4(1); //got but not active
            } else {
                $io_user->setB4(3); //done
            }
        }
        if (isset($d['b5'])) {
            if ($d['b5'] === UserBonus::STATUS_ACTIVE) {
                $io_user->setB5(2); //active
            } elseif(in_array($d['b5'], [
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setB5(1); //got but not active
            } else {
                $io_user->setB5(3); //done
            }
        }
        $io_user->setWb($d['wb']?? 0);
        if (isset($d['wb_state'])) {
            if ($d['wb_state'] === UserBonus::STATUS_ACTIVE) { // can't be active thou
                $io_user->setWbState(2); //active
            } elseif(in_array($d['wb_state'], [
                UserBonus::STATUS_PENDING,
                UserBonus::STATUS_NOT_ACTIVATED,
                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                UserBonus::STATUS_WAITING_FOR_PAYMENT,
            ])) {
                $io_user->setWbState(1); //got but not active
            } else {
                $io_user->setWbState(3); //done
            }
        }
        // top game data
        if (isset($d['top_game_id'])) $io_user->setTopGameId($d['top_game_id']);
        if (isset($d['top_game_name'])) $io_user->setTopGameName($d['top_game_name']);
        if (isset($d['top_game_provider'])) $io_user->setTopGameProvider($d['top_game_provider']);
        if (isset($d['top_game_system'])) $io_user->setTopGameSystem($d['top_game_system']);

        if (isset($d['top_provider'])) $io_user->setTopProvider($d['top_provider']);
        if (isset($d['top_provider_system'])) $io_user->setTopProviderSystem($d['top_provider_system']);

        $io_user->save();
        return $io_user;
    }

    public static function syncUser(MUser $user, $send_data = true): ?CustomerIoUser
    {
        $data = self::selectUsersData([$user->getId()]);
        if (!isset($data[$user->getId()])) {
            Log::error(__CLASS__." No data for user #".$user->getId());
            return null;
        }
        $d = $data[$user->getId()];
        $io_user = self::updateData($user->getId(), $d);
        if ($send_data) {
            try {
                CustomerIo::syncCustomerIoUser($io_user);
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }
        }
        return $io_user;
    }

    public function syncData(): void
    {
        try {
            CustomerIo::syncCustomerIoUser($this);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    public static function selectUsersData(array $user_ids): array
    {

        $query = MUser::query();
        $query->whereIn('m_users.id', $user_ids);

        //deposits
        $query->leftJoin(DB::raw("(select user_id, sum(sum) as total_deposit, COUNT(payout_transactions.id) as deposit_cnt, min(date) as first_dep_date, max(date) as last_dep_date
                                        from payout_transactions
                                        where user_dep_n > 0
                                        group by user_id) as deposits"), function ($join) {
            $join->on("deposits.user_id", '=', 'm_users.id');
        });

        //deposits in last 7 days
        $query->leftJoin(DB::raw("(select user_id, sum(sum) as total_deposit
                                        from payout_transactions
                                        where user_dep_n > 0
                                        and date >= '" . now()->subDays(7)->format('Y-m-d H:i:s') . "'
                                        group by user_id) as deps_7d"), function ($join) {
            $join->on("deps_7d.user_id", '=', 'm_users.id');
        });

        //deposits in last 30 days
        $query->leftJoin(DB::raw("(select user_id, sum(sum) as total_deposit
                                        from payout_transactions
                                        where user_dep_n > 0
                                        and date >= '" . now()->subDays(30)->format('Y-m-d H:i:s') . "'
                                        group by user_id) as deps_30d"), function ($join) {
            $join->on("deps_30d.user_id", '=', 'm_users.id');
        });

        // partner
        $query->leftJoin(DB::raw("(select id as click_id, promotion_id, link_id as pap_link_id
                                            from papdb.clicks
                                           ) as pap_clicks"), function ($join) {
            $join->on('pap_clicks.click_id', '=', 'm_users.click_id');
        })
            ->leftJoin(DB::raw("(select id as promotion_id, casino_id, user_id as pap_user_id
                                            from papdb.promotions
                                           ) as pap_promotions"), function ($join) {
                $join->on('pap_promotions.promotion_id', '=', 'pap_clicks.promotion_id');
            })
            ->leftJoin(DB::raw("(select id as pap_user_id, first_name as pap_first_name, second_name as pap_second_name, email as pap_email
                                            from papdb.users
                                           ) as pap_users"), function ($join) {
                $join->on('pap_users.pap_user_id', '=', 'pap_promotions.pap_user_id');
            });

        // personal data
        $query->leftJoin('personal_data', 'personal_data.user_id', 'm_users.id');
        //telegram clients
        $query->leftJoin('crm_admin.telegram_clients', 'crm_admin.telegram_clients.m_user_id', 'm_users.id');


        $select = "
            m_users.id as user_id
            , m_users.email as email
            , m_users.locale as lang
            , m_users.created_at as reg_date
            , m_users.created_at as reg_date
            , m_users.phone as phone
            , m_users.currency as curr_id
            , m_users.rank as 'rank'
            , m_users.blocked as blocked
            , m_users.once_pass as phone_token
            , m_users.email_verification_token as email_token
            , m_users.last_tz as timezone
            , deposits.first_dep_date as first_dep_date
            , deposits.last_dep_date as last_dep_date
            , deposits.last_dep_date as last_dep_date
            , deposits.total_deposit as dep_sum
            , deposits.deposit_cnt as dep_cnt
            , deps_7d.total_deposit as dep_sum_7d
            , deps_30d.total_deposit as dep_sum_30d
            , personal_data.name as first_name
            , personal_data.surname as last_name
            , personal_data.birthday as birthday
            , pap_users.pap_email as partner
            , crm_admin.telegram_clients.tg_id as tg_id
            , crm_admin.telegram_clients.locale as lang_tg
        ";
        $query->selectRaw($select);
        $data = $query->get()->keyBy('user_id')->toArray();

        $dep1_bp_ids = [];
        $dep1fs_bp_ids = [];
        $dep2_bp_ids = [];
        $dep2fs_bp_ids = [];
        $dep3_bp_ids = [];
        $dep3fs_bp_ids = [];
        $ndep1fs_bp_ids = [];
        $ndep2fs_bp_ids = [];
        // get welcome bonus pack bonuses data
        foreach (Wallet::activeCurrencies() as $currency_id) {
            $dep1_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_DEP, 1)?->getId()?? -1;
            $dep1fs_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_FS, 1)?->getId()?? -1;
            $dep2_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_DEP, 2)?->getId()?? -1;
            $dep2fs_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_FS, 2)?->getId()?? -1;
            $dep3_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_DEP, 3)?->getId()?? -1;
            $dep3fs_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_FS, 3)?->getId()?? -1;
            $ndep1fs_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_FS, 4)?->getId()?? -1;
            $ndep2fs_bp_ids[] = BonusPatterns::getRegPattern(Wallet::currencyName($currency_id), BonusPatterns::RB_TYPE_FS, 5)?->getId()?? -1;
        }
        // all wb patterns
        $wb_patters_ids = array_merge($dep1_bp_ids, $dep1fs_bp_ids, $dep2_bp_ids, $dep2fs_bp_ids, $dep3_bp_ids, $dep3fs_bp_ids, $ndep1fs_bp_ids, $ndep2fs_bp_ids);

        // wb bonuses
        $query = UserBonus::query();
        $select = "
            user_id, pattern_id, status
        ";
        $query->whereIn('user_id', $user_ids);
        $query->whereIn('pattern_id', $wb_patters_ids);
        $query->selectRaw($select);

        $bdata = $query->get();
        foreach ($bdata as $bd) {
            if (in_array($bd['pattern_id'], $dep1_bp_ids)) {
                $data[$bd['user_id']]['b1'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $dep1fs_bp_ids)) {
                $data[$bd['user_id']]['b1fs'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $dep2_bp_ids)) {
                $data[$bd['user_id']]['b2'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $dep2fs_bp_ids)) {
                $data[$bd['user_id']]['b2fs'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $dep3_bp_ids)) {
                $data[$bd['user_id']]['b3'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $dep3fs_bp_ids)) {
                $data[$bd['user_id']]['b3fs'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $ndep1fs_bp_ids)) {
                $data[$bd['user_id']]['b4'] = $bd['status'];
            }
            if (in_array($bd['pattern_id'], $ndep2fs_bp_ids)) {
                $data[$bd['user_id']]['b5'] = $bd['status'];
            }
        }

        $welcome_bonuses = UserBonus::whereIn('user_id', $user_ids)
            ->where('type', UserBonus::TYPE_WELCOME_BONUS_PACK)
            ->selectRaw('user_id, tries, status')
            ->get();
        foreach ($welcome_bonuses as $wb) {
            $data[$wb->user_id]['wb'] = $wb->tries;
            $data[$wb->user_id]['wb_state'] = $wb->status;
        }

        foreach ($user_ids as $user_id) {
           $top_game = UserGameStat::getUserTopGame($user_id);
           if (!empty($top_game)) {
               $data[$user_id]['top_game_id'] = $top_game->game_id;
               $data[$user_id]['top_game_name'] = $top_game->game_name;
               $data[$user_id]['top_game_provider'] = $top_game->ggroup;
               $data[$user_id]['top_game_system'] = $top_game->gsystem;
           }
           $top_provider = UserGameStat::getUserTopProvider($user_id);
           if (!empty($top_provider)) {
               $data[$user_id]['top_provider'] = $top_provider->group_id;
               $data[$user_id]['top_provider_system'] = $top_provider->gsystem;
           }
        }

        return $data;
    }
}
