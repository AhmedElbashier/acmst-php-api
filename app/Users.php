<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{

    protected $fillable = ["name", "username", "password", "role", "lastLogin"];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships


    protected $hidden = [
        'password',
    ];
}
