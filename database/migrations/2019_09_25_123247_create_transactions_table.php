<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{

    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->integer('leftover');
            $table->string('payments');
            $table->integer('userId');
            $table->integer('studantId');
            $table->string('PaymentMethod');
            $table->string('stdYear');
            $table->date('StatmentDate')->nullable();
            $table->string('StatmentNumber')->nullable();
            $table->integer('InvoiceNumber')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('transactions');
    }
}
