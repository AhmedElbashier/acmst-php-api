<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paymnet extends Model
{

    protected $fillable = ["id", "amount", "PaymentDate", "PaymentTo", "PaymentFrom", "PaymentType", "PaymentMethod", "StatmentNumber", "StatmentDate", "userId"];

    protected $dates = ["PaymentDate"];

    // Relationships

}
