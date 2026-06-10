<?php

namespace App\Http\Controllers;



use App\Helpers\MetaTags;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HallOfFameController extends Controller
{
    public function index(Request $request)
    {
        // Set meta-tags.
        $this->metaTags = new MetaTags();
        $this->metaTags->title = __('meta.hofTitle');
        $this->metaTags->description = __('meta.hofDescription');
        $this->metaTags->keywords = __('meta.hofKeywords');

        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        if ($request->ajax()) {

            if ($request->get('table', null) !== null) {
                $date = $request->get('date', null);
                if ($date === null || $date === 'all') {
                    $month = null;
                } else {
                    $month = Carbon::createFromFormat('Y-m-d', $date);
                }

                $this->renderAdaptive('hall-of-fame.table', ['month' => $month]);
            } else {
                $this->renderAdaptive('hall-of-fame.index', [], []);
            }

        } else {
            $this->renderAdaptive('hall-of-fame.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }
}
