<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CommityLog extends Model {

    protected $fillable = ["CommityHead", "isMedicalFit", "isCommityFit"];

    protected $dates = [];

    public static $rules = [
        "studant" => "numeric",
        "CommityHead" => "required",
        "isMedicalFit" => "required",
        "isCommityFit" => "required",
    ];

    // Relationships

}
