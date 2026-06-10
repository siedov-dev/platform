<?php

namespace App\Models\Slc\Achievements\Data;

use App\MUser;

interface SlcAchievementData
{
    /**
     * Возвращает дополнительные параметры необходимые достижению
     * ['param name' => 'field_name'] где field_name один из float_param/int_param/string_param
     * Используется в Crm
     * @return array
     */
    public function getRequiredParams(): array;

    /**
     * Возвращает текущее значение достижения
     * @param MUser $user
     * @return int
     */
    public function getCurrentValue(MUser $user): int;

    /**
     * Возвращает строку цели достижения
     * @param MUser $user
     * @return string
     */
    public function getGoalString(MUser $user): string;

    /**
     * Возвращает строку значение достижения
     * @param MUser $user
     * @return string
     */
    public function getValueHint(MUser $user): string;

    /**
     * Возвращает процент выполнения достижения
     * @param MUser $user
     * @return int
     */
    public function getCurrentPercent(MUser $user): int;
}
