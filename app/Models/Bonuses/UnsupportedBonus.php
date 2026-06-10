<?php

namespace App\Models\Bonuses;

class UnsupportedBonus extends KUserBonus
{

    public function __construct(array &$attributes = [], array &$meta = [])
    {
        $attributes['status'] = static::STATUS_ERROR;
        $meta = [];
        parent::__construct($attributes, $meta);
    }

    public function getName(): string
    {
        return "Unsupported bonus";
    }

    public function getTitle(): string
    {
        return "Unsupported bonus";
    }

    public function getShortDescription(): string
    {
        return view('common.bonus-descriptions.t_unsupported')->render();
    }

    public function activate(): bool
    {
        return false;
    }

}
