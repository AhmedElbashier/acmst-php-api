<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AutoCash extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        "transId" => "unsigned",
    ];

    public $timestamps = false;

    // Relationships

}
