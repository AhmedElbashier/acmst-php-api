<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtraTransactions extends Model
{

    protected $fillable = [
        'amount',
        'userId',
        'studantId',
        'PaymentName',
        'PaymentType',
        'PaymentMethod',
        'StatmentDate',
        'StatmentNumber'
    ];

    protected $dates = [];

    public static $rules = [
        "amount" => "unsigned",
        "userId" => "unsigned",
        "studantId" => "unsigned",
    ];

    // Relationships

}
