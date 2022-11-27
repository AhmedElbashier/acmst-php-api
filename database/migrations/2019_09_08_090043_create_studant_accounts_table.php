<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudantAccountsTable extends Migration
{

    public function up()
    {
        Schema::create('studant_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('studantId');
            $table->integer('amount');
            $table->integer('scolarship')->default('1');
            $table->string('scolarshipType')->nullable();
            $table->integer('tolls');
            $table->integer('registration');
            $table->integer('loan');
            $table->string('currency');
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('studant_accounts');
    }
}
