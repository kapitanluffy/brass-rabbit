<?php

namespace App\Repositories\Contact;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'email',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
