<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymnetsTable extends Migration
{

    public function up()
    {
        Schema::create('paymnets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->string('PaymentTo');
            $table->string('PaymentFrom');
            $table->string('PaymentType');
            $table->date('PaymentDate');
            $table->string('PaymentMethod');
            $table->date('StatmentDate')->nullable();
            $table->string('StatmentNumber')->nullable();
            $table->integer('userId');
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('paymnets');
    }
}
