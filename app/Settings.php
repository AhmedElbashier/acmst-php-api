<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model {

    protected $fillable = ["field1", "field2", "field3", "field4", "field5", "field6"];

    protected $dates = [];

    public static $rules = [
        "field1" => "required",
        "field2" => "required",
        "field3" => "required",
        "field4" => "required",
        "field5" => "required",
        "field6" => "required",
    ];

    // Relationships

}
