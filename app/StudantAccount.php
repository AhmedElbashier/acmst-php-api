<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudantAccount extends Model
{

    protected $fillable = [
        "studantId",
        "amount",
        "scolarship",
        "scolarshipType",
        "tolls",
        "loan",
        "currency",
        "registration"
    ];

    protected $dates = [];

    public static $rules = [
        "studantId" => "unsigned",
        "amount" => "unsigned",
        "scolarshop" => "unsigned",
        "tolls" => "unsigned",
        "loan" => "unsigned",
        "currency" => "required",
    ];

    // Relationships

}
