<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudantInstallment extends Model
{

    protected $fillable = ["StudentId", "PaymentId", "checked", "checkedBy", "Notes"];

    protected $dates = [];

    public static $rules = [
        "StudentId" => "unsigned",
        "PaymentId" => "unsigned",
        "checked" => "required",
        "checkedBy" => "unsigned",
        "Notes" => "required",
    ];

    // Relationships

}
