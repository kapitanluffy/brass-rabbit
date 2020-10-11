<?php

namespace App\Repositories\Template;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'subject',
        'message'
    ];
}
