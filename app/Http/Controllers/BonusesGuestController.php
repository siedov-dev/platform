<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated
 */
class BonusesGuestController extends Controller
{
    const BONUSES = [
        'happy-st-patrick1' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],
        'happy-st-patrick2' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],
        'happy-st-patrick3' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],
        'happy-st-patrick4' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],
        'happy-st-patrick5' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],
        'happy-st-patrick6' => [
            'name' => 'Happy St. Patrick Days!!!',
            'amount' => '7 777 777 UAH',
            'end' => '2023-12-18 00:00'
        ],

    ];

    public function index(Request $request)
    {

        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.bonusesTitle');
        $this->metaTags->description = __('meta.bonusesDesc');

        $this->renderAdaptive('bonuses.bonuses-guest', ['bonuses' => self::BONUSES], []);
        return $this->sendPreparedResponse();
    }

    public function show($locale, $bonus, Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.bonusesTitle');
        $this->metaTags->description = __('meta.bonusesDesc');

        $this->renderAdaptive('bonuses.'.$bonus, ['bonus' => self::BONUSES[$bonus], 'bonuses' => self::BONUSES], ['layouts.main']);
        return $this->sendPreparedResponse();
    }


}
