<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{

    protected $fillable = [
        'amount',
        'leftover',
        'payments',
        'userId',
        'studantId',
        'PaymentMethod',
        'StatmentDate',
        'StatmentNumber',
        'ReceiptNumber',
        "stdYear"
    ];

    protected $dates = [];

    public static $rules = [
        "amount" => "unsigned",
        "leftover" => "unsigned",
        "userId" => "unsigned",
        "studantId" => "unsigned",
    ];

    // Relationships

}
