<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{

    protected $fillable = ['id',"name", "type", "department", "price"];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships


    protected $hidden =[ ];
}
