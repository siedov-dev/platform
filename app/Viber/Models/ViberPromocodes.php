<?php

namespace App\Viber\Models;

use App\Viber\ViberApi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class ViberPromocodes
 * @package App\Viber\Models
 * @property int $id
 * @property int $status
 * @property int $type
 * @property string $promocode
 * @property int $bonus_type
 * @property string $bonus_params
 * @property Carbon $active_to
 * @property Carbon $sent_at
 * @property string $message
 * @property string $user_list
 * @property string $user_received
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ViberPromocodes extends Model
{
    //
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SENT = 2;
    //
    protected $dates = [
        'active_to',
        'send_at',
        'crated_at',
        'updated_at',
    ];

    public static function process_timer()
    {
        try {

            /** @var ViberPromocodes $promo */
            $promo = ViberPromocodes::where('status', ViberPromocodes::STATUS_PENDING)
                ->where('send_at', '<', now())
                ->first();

            if ($promo !== null) {
                Log::channel('viber')->info("sending promocode $promo->id message to users");
                $promo->status = ViberPromocodes::STATUS_PROCESSING;
                $promo->save();

                if ($promo->user_list == null) {

                    $users = ViberUser::all();
                    /** @var ViberUser $v_user */
                    foreach ($users as $v_user) {
                        try {
                            Log::channel('viber')->info('sending message to user id#' . $v_user->user_id . ' v_id#' . $v_user->viber_id);
                            $v_user->sendText($promo->message);
                        } catch (\Exception $e) {
                            Log::channel('viber')->error($e->getMessage());
                        }
                    }

                    $promo->status = ViberPromocodes::STATUS_SENT;
                    $promo->save();
                }
            }

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }

    }
}
