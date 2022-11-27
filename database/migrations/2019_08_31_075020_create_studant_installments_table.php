<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudantInstallmentsTable extends Migration
{

    public function up()
    {
        Schema::create('studant_installments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('StudentId');
            $table->integer('PaymentId');
            $table->boolean('checked');
            $table->integer('checkedBy');
            $table->string('Notes')->nullable();
            $table->string('year');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('studant_installments');
    }
}
