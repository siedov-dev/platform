<?php

namespace App\Models\Bonuses\Contracts;

interface FirstDepBonusInfo
{
    /**
     * get 5 elements long array with fs => min_dep_amount data
     * @return array
     */
    public function freespinsConditions(): array;
}
