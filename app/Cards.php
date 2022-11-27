<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Cards extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        "semesterId" => "unsigned",
        "studantId" => "unsigned",
        "userId" => "unsigned",
    ];

    // Relationships

}
