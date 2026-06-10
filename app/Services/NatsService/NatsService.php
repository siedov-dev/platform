<?php

namespace App\Services\NatsService;

use Basis\Nats\Client;
use Basis\Nats\Configuration;

/**
 * @docs https://github.com/basis-company/nats.php
 */
final class NatsService
{
    private string $token = "";
    private ?Client $client = null;

    private static ?NatsService $_service = null;

    private function __construct()
    {
        $this->token = config('services.natsToken');
        $configuration = new Configuration(
            host: 'localhost',
            token: $this->token,
        );

        $configuration->setDelay(1);
        $configuration->reconnect = true;
        $this->client = new Client($configuration);
    }

    public static function getInstance(): NatsService
    {
        if (self::$_service === null) {
            self::$_service = new NatsService();
        }

        return self::$_service;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function ping(): bool
    {
        return $this->client->ping();
    }
}
