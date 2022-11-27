<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{

    protected $fillable = ["operation", "operation_id", "person_id", "atdate", "notes"];

    protected $dates = ["atdate"];

    public static $rules = [
        "operation" => "required",
        "person_id" => "numeric",
        "person_id" => "numeric",
    ];

    // Relationships

}
