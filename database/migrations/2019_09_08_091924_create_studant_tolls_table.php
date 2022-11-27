<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudantTollsTable extends Migration
{

    public function up()
    {
        Schema::create('studant_tolls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('year');
            $table->integer('amount');
            $table->integer('registration');
            $table->integer('LoanNumber');
            $table->string('program');
            $table->string('currency');
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('studant_tolls');
    }
}
