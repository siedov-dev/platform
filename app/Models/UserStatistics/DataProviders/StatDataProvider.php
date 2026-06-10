<?php

namespace App\Models\UserStatistics\DataProviders;

use App\MUser;

interface StatDataProvider
{
    public function getValue(MUser $user): mixed;
}
