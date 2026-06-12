<?php

namespace App\Http\Controllers;

use App\Helpers\Device;
use App\Helpers\MetaTags;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $metaTags;

    private $_preparedContent = '';

    private array $_jsonData = [];

    protected function createDefaultMetaTags(): MetaTags
    {
        $metaTags = new MetaTags();
        $metaTags->title = __('meta.mainTitle');
        $metaTags->description = __('meta.mainDescription');
        $metaTags->keywords = __('meta.mainKeywords');
        $metaTags->image = url('img/banners/main-banner.jpg');
        return $metaTags;
    }


    protected function wantsJsonResponse(): bool
    {
        $request = request();
        return $request->query('format') === 'json'
            || strtolower((string) $request->headers->get('X-Response-Format')) === 'json';
    }


    protected function collectJsonData(array $params): void
    {
        $this->_jsonData = array_merge($this->_jsonData, $params);
    }

    /**
     * Очищает данные перед отдачей в JSON, 
     * чтобы случайно не слить пароли и системные токены.
     */
    protected function sanitizeJsonData(array $data): array
    {
        if (isset($data['user']) && is_object($data['user']) && method_exists($data['user'], 'makeHidden')) {
            $data['user']->makeHidden([
                'password', 
                'remember_token', 
                'two_factor_secret', 
                'email_verified_at', 
                'auth_token'
            ]);
        }
        
        return $data;
    }


    protected function viewOrJson($view, array $params = [])
    {
        if ($this->wantsJsonResponse()) {
            // Прогоняем данные через санитайзер
            return response()->json($this->sanitizeJsonData($params));
        }
        return view($view, $params);
    }


    protected function render($view, $params = [], $layouts = [])
    {
        if ($this->wantsJsonResponse()) {
            $this->collectJsonData($params);
            return;
        }

        $content = view($view, $params)->render();
        $metaTags = $this->metaTags === null ? $this->createDefaultMetaTags() : $this->metaTags;
        foreach ($layouts as $layout) {
            $content = view($layout, ['content' => $content, 'metaTags' => $metaTags])->render();
        }
        $this->_preparedContent .= $content;
    }

    protected function renderAdaptive($view, $params = [], $layouts = [])
    {
        if ($this->wantsJsonResponse()) {
            $this->collectJsonData($params);
            return;
        }

        foreach ($layouts as $k => $v) {
            $layouts[$k] = Device::detect() . '.' . $v;
        }
        $this->render(Device::detect() . '.' . $view, $params, $layouts);
    }


    protected function sendPreparedResponse(bool $allowCaching = false, array $headers = [], array $cookies = [])
    {
        if ($this->wantsJsonResponse()) {
            // Прогоняем собранные данные через санитайзер
            $res = response()->json($this->sanitizeJsonData($this->_jsonData));
        } else {
            $res = response(($this->metaTags === null ? '' : $this->metaTags->render()) . $this->_preparedContent);
        }
        
        if ($allowCaching) {
            $res->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
        
        foreach ($headers as $k => $v) {
            $res->header($k, $v);
        }
        foreach ($cookies as $k => $v) {
            $res->cookie($k, $v);
        }
        
        return $res;
    }


    protected function back()
    {
        if (isset($_SERVER['HTTP_X_REDIRECT_TO'])) {
            return redirect($_SERVER['HTTP_X_REDIRECT_TO']);
        }
        return back();
    }
}