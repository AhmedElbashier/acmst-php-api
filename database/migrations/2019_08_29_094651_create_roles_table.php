<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{

    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('acceptance');
            $table->boolean('approval');
            $table->boolean('registration');
            $table->boolean('scolarship');
            $table->boolean('installment');
            $table->boolean('exams');
            $table->boolean('settings');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('roles');
    }
}
