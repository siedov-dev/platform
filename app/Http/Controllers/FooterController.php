<?php

namespace App\Http\Controllers;

use App\Helpers\Device;
use App\Helpers\MetaTags;
use App\Models\FooterLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FooterController extends Controller
{

    public function footerPage(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        /** @var FooterLink $link */
        $link = FooterLink::find($request->input('id'));
        if ($link) {
            return $this->viewOrJson(Device::detect().'.footer.info', ['content' => $link->getPage()]);
        }
        throw new NotFoundHttpException();
    }
}
