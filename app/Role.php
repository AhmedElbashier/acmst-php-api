<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    protected $fillable = ["name", "create", "delete", "update", "view"];

    protected $dates = [];

    public static $rules = [
        "name" => "required",
        "create" => "required",
        "delete" => "required",
        "update" => "required",
        "view" => "required",
    ];

    // Relationships

}
