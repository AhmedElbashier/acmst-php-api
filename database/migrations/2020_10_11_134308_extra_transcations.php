<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtraTranscations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('extra_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->string('PaymentMethod');
            $table->integer('userId');
            $table->integer('studantId');
            $table->date('StatmentDate')->nullable();
            $table->string('StatmentNumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
