<?php

namespace App\Http\Controllers;

use App\Models\Promos\PromoItem;
use App\MUser;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function promos(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $user = MUser::getAuth();
        $items = PromoItem::getAvailable($user);

        $this->renderAdaptive('promos.promos', compact('items'), []);
        return $this->sendPreparedResponse();
    }

    public function descriptionPage(string $locale, int $promo_id, Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $item = PromoItem::find($promo_id);
        $user = MUser::getAuth();
        $items = PromoItem::getAvailable($user)->whereNotIn('id', [$promo_id]);

        $other_promos = $items->shuffle();

        $this->renderAdaptive('promos.promo-descr', compact('item', 'items', 'other_promos'), []);
        return $this->sendPreparedResponse();
    }
}
