<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudantTolls extends Model
{

    protected $fillable = [
        "year",
        "amount",
        "registration",
        "program",
        "LoanNumber",
        "currency",
    ];

    protected $dates = [];

    public static $rules = [
        "year" => "unsigned",
        "amount" => "unsigned",
        "registration" => "unsigned",
    ];

    // Relationships

}
