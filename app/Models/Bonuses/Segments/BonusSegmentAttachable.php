<?php

namespace App\Models\Bonuses\Segments;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface BonusSegmentAttachable
{
    public function bonusSegments(): BelongsToMany;
}
