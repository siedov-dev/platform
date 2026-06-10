<?php

namespace App\Mail\CheckMail;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class CheckMail
{
    private string $gateId = "01kq9mn20e79fym8zwyq9sb5ey";
    private string $baseUrl = "https://api.usercheck.com/v0/gates/";
    private string $key = "prd_aGnTf80G6kX2c0epCznVdobVlyz5";

    private static ?CheckMail $_instance = null;

    private function __construct() {}

    public function checkEmail(string $email): array
    {
        $ip = request()->ip();
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->block();
        }

        [$name, $domain] = explode('@', $email);

        // --- GMAIL NORMALIZATION ---
        $cleanName = $name;
        if ($domain === 'gmail.com') {
            $cleanName = explode('+', $name)[0];
            $cleanName = str_replace('.', '', $cleanName);
        }
        $normalizedEmail = $cleanName . '@' . $domain;

        // --- RATE LIMIT ---
        if (RateLimiter::tooManyAttempts('mail-ip:'.$ip, 20)) {
            Log::channel('mail')->warning("RATE LIMIT IP {$ip}");
            return $this->block();
        }
        RateLimiter::hit('mail-ip:'.$ip, 60);

        // --- CACHE (Check normalized version) ---
        $cacheKey = 'mail_v14:' . md5($normalizedEmail);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // --- 1. DOMAIN BLACKLIST (The "coledu.us" Fix) ---
        $badDomains = [
            'coledu.us', 'edu.us', 'temp-mail.org', 'guerrillamail.com', 'sharklasers.com',
            '10minutemail.com', 'mailinator.com', 'dispostable.com', 'getnada.com'
        ];
        if (in_array($domain, $badDomains)) {
            Log::channel('mail')->info("DOMAIN BLOCK: {$domain}");
            return $this->cache($cacheKey, $this->block());
        }

        // --- 2. LOCAL GIBBERISH FILTER ---
        if ($this->isGibberish($name)) {
            Log::channel('mail')->info("GIBBERISH BLOCK: {$email}");
            return $this->cache($cacheKey, $this->block());
        }

        // --- 3. PATTERN REPETITION FILTER (The "EvaStark" Fix) ---
        // Убираем цифры и проверяем, не идет ли спам похожих имен (Eva53, Eva12, Eva...)
        $alphaRoot = preg_replace('/[^a-z]/', '', $name);
        if (strlen($alphaRoot) >= 4) {
            $rootCacheKey = 'mail_root_v1:' . $alphaRoot;
            $rootCount = Cache::get($rootCacheKey, 0);
            if ($rootCount > 3) { // Больше 3 регистраций на похожий корень за час
                Log::channel('mail')->warning("PATTERN REPETITION BLOCK: {$email}");
                return $this->cache($cacheKey, $this->block());
            }
            Cache::put($rootCacheKey, $rootCount + 1, now()->addHour());
        }

        // --- 4. LOCAL BLACKLIST & BASIC CHECKS ---
        $blacklist = [
            'tahi', 'uxuh', 'quc', 'anuv', 'trash', 'spam', 'ebi', 'tyler', 'giuliana', 'allison', 'ison', 'oefe', 'icq',
            'admin', 'support', 'root', 'webmaster', 'administrator', 'office', 'manager', 'billing',
            'botmail', 'mailbot', 'proxy', 'testing', 'shady', 'unknown', 'fake', 'temp', 'noreply',
            'zakaz', 'otzyv', 'sklad', 'prodazh', 'promo', 'reklama', 'marketing', 'info', 'sale'
        ];
        foreach ($blacklist as $word) {
            if (str_contains($name, $word)) return $this->cache($cacheKey, $this->block());
        }

        if (strlen($name) < 3 || (ctype_digit($name) && strlen($name) > 6)) {
            return $this->cache($cacheKey, $this->block());
        }

        // --- 5. DNS CHECK ---
        if ($domain && !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            Log::channel('mail')->info("DNS FAIL: {$domain}");
            return $this->cache($cacheKey, $this->block());
        }

        // --- 6. API CHECK (With Fail-Safe) ---
        $apiFailed = false;
        try {
            $response = Http::withToken($this->key)->timeout(5)->retry(2, 200)->post($this->baseUrl . $this->gateId . "/decisions", [
                'email' => $email,
                'ip'    => $ip,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $action = $data['decision']['action'] ?? $data['action'] ?? 'allow';
                if ($action === 'block' || ($data['verdict'] ?? '') === 'undeliverable') {
                    return $this->cache($cacheKey, $this->block());
                }
            } else {
                $apiFailed = true;
            }
        } catch (\Throwable $e) {
            Log::channel('mail')->error("API EXCEPTION: " . $e->getMessage());
            $apiFailed = true;
        }

        // --- 7. FALLBACK ---
        if ($apiFailed) {
            return $this->cache($cacheKey, $this->allowSoft());
        }

        return $this->cache($cacheKey, $this->allow());
    }

    private function isGibberish(?string $name): bool
    {
        if (!$name || strlen($name) < 3) return false;

        // Rule 0: Entropy check (Mixing letters and digits like "SharlaBa235tonob5ev")
        // Если буквы и цифры чередуются более 3 раз - это генерация
        if (preg_match_all('/[a-z]\d|\d[a-z]/i', $name) >= 3) return true;
        
        // Rule 1: No vowels
        if (!preg_match('/[aeiouy]/i', $name)) return true;
        
        // Rule 2: Consonant clusters (4+ in a row)
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]{4,}/i', $name)) return true;
        
        // Rule 3: Repeating characters (aaaaa)
        if (preg_match('/(.)\1{4,}/i', $name)) return true;
        
        // Rule 4: Digit density
        $digitCount = preg_match_all('/\d/', $name);
        if ($digitCount > 5 || ($digitCount >= 4 && $digitCount > (strlen($name) / 2))) return true;
        
        // Rule 5: Too many dots or weird separators
        if (substr_count($name, '.') >= 4 || str_contains($name, '..')) return true;
        
        // Rule 6: Suspicious keyboard clusters
        $bad = [
            'qet', 'kcv', 'vbt', 'fzk', 'qrs', 'zdf', 'fra', 'asdfgh', 'qwerty',
            'asd', 'zxc', 'dfg', 'ghj', 'jkl', 'yui', 'uio', 'iop', 'vbn', 'bnm',
            'qaz', 'wsx', 'edc', 'rfv', 'tyu', 'vbh', 'nmj', 'cvb', 'xcv'
        ]; 
        foreach ($bad as $word) {
            if (str_contains($name, $word)) return true;
        }
        
        // Rule 7: Specific bot patterns (Length + Numbers)
        if (strlen($name) > 15 && preg_match('/\d{3,}/', $name)) return true;

        return false;
    }

    private function block(): array
    {
        return [
            'verdict'    => 'block',
            'status'     => 'disposable',
            'mx'         => false,
            'disposable' => true
        ];
    }

    private function allow(): array
    {
        return ['verdict' => 'allow', 'status' => 'ok', 'mx' => true, 'disposable' => false];
    }

    private function allowSoft(): array
    {
        return ['verdict' => 'allow', 'status' => 'soft', 'mx' => true, 'disposable' => false];
    }

    private function cache($key, $data)
    {
        $ttl = ($data['verdict'] ?? '') === 'allow' ? now()->addHours(6) : now()->addDays(3);
        Cache::put($key, $data, $ttl);
        return $data;
    }

    public static function getInstance(): self {
        return self::$_instance ??= new self();
    }

    public static function email(string $email): array {
        return self::getInstance()->checkEmail($email);
    }
}
