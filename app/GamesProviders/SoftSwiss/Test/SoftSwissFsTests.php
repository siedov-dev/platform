<?php

namespace App\GamesProviders\SoftSwiss\Test;

use App\GamesProviders\SoftSwiss\SoftSwissProviderApi;
use App\MUser;

class SoftSwissFsTests
{
    public static function test1($user_id)
    {
        $user = MUser::find($user_id);
        $api = new SoftSwissProviderApi();
        $api->freespinsCreate($user_id.'RUB', $user, 'test:'. time(), 'RUB', ['acceptance:test'], 10, now()->addDays(1));
    }

    public static function test2($user_id)
    {
        $user = MUser::find($user_id);
        $api = new SoftSwissProviderApi();
        $api->freespinsCreate($user_id.'RUB', $user, 'test2:'. time(), 'RUB', ['acceptance:test', 'acceptance:secondary_test'], 5, now()->addDays(1));
    }

    public static function test3($user_id)
    {
        $user = MUser::find($user_id);
        $api = new SoftSwissProviderApi();
        $api->freespinsCreate($user_id.'RUB', $user, 'test:'. time(), 'RUB', ['acceptance:test'], 10, now()->addDays(1));
    }
}
