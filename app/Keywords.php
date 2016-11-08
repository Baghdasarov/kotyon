<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keywords extends Model
{
    protected $table = 'keywords';

    protected $fillable = [
        'keyword',
        'user_id',
        'channel_id',
        'preferred',
        'group',
        'country',
        'lang',
        'created_at',
        'updated_at',
    ];
}
