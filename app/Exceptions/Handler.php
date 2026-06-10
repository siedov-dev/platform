<?php

namespace App\Exceptions;

use App\Helpers\Localization;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $locale = Localization::getLocale($request);
        if (empty($locale)) $locale = 'en';
        if($this->isHttpException($e) && $e->getStatusCode() === 404)
        {
            // not found
            $uri = $request->getRequestUri();
            $s = $request->segment(1);
            if (!preg_match('#^\w{2}$#', $s)){
                return redirect('/' . $locale. $uri);
            }
            return redirect()->guest('/'.$locale);
        }

        if($this->isHttpException($e) && $e->getStatusCode() === 500)
        {
            // internal error
            return redirect()->guest('/'.$locale);
        }

        if($e instanceof \Illuminate\Session\TokenMismatchException){
            // token mismatch is a security concern, ensure logout.
            Auth::logout();
            if ($request->ajax()) {
                return response('Session expired', 401);
            }

            return redirect()->guest('/'.$locale);
        }

        return parent::render($request, $e);
    }
}
