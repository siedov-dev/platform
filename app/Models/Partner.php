<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * Класс партнера.
 */
class Partner extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'partners';

    public $table = 'users';
    public $timestamps = true;
}
