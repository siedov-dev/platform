<?php

namespace App\Http\Controllers;

use App\Models\TvBet\TVBetToken;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TVBetController extends Controller
{
    private $_clientId;
    private $_secret;

    public function __construct()
    {
        $this->_clientId = config('game_providers_keys.tvBetClientId');
        $this->_secret = config('game_providers_keys.tvBetSecret');
    }


    public function play()
    {
        $this->renderAdaptive('games.tv-bet_2', [], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }


    public function frame()
    {
        $meta = $this->createDefaultMetaTags();
        $meta->title = 'Ставки на TvBet 🎲 Live ставки, быстрые игры Кено в казино 🐙 Кракен';
        $meta->keywords = 'Ставки на TvBet, Live ставки, быстрые игры Кено, Кракен';
        $meta->description = 'Ставки на TvBet, Live ставки, быстрые игры Кено, Кракен';

        $this->renderAdaptive('games.tv-bet-frame_2', [], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }

    public function usersAuth(Request $request)
    {
        Log::info(__CLASS__.'auth ');
        $postBody = file_get_contents("php://input");
        $data = JWT::decode($postBody, $this->_secret);

        Log::info('TVBET: /users/auth' . var_export($data, true));

        if (property_exists($data, 'data')) {
            $d = json_decode($data->data, true);

            if (property_exists($d, 'RefreshToken')) {
                $tvBetToken = $this->getTVBetToken($d->RefreshToken);
                if ($tvBetToken->user !== null) {
                    $tvBetToken->generateToken();
                    $tvBetToken->save();
                    return $this->createNormalResponse([
                        'RefreshToken'  => $tvBetToken->token,
                        'PartnerUserId' => $tvBetToken->getCurrency().'__'.$tvBetToken->user->login,
                        'CurrencyCode ' => $tvBetToken->getCurrency(),
                        'IsTest'        => false,
                    ]);
                }
            }
        }

        return $this->createErrorResponse();
    }



    public function paymentsMake(Request $request)
    {

    }



    private function getTVBetToken(string $token) :? TVBetToken
    {
        return TVBetToken::where('token', $token)->first();
    }


    private function createNormalResponse(array $value)
    {
        $data = [
            'ErrorCode' => 200,
            'Message'   => '',
            'Value'     => $value,
        ];
        return json_encode($data);
    }


    private function createErrorResponse()
    {
        $data = [
            'ErrorCode' => 103805,
            'Message'   => ' ',
            'Value'     => null,
        ];
        return json_encode($data);
    }
}
