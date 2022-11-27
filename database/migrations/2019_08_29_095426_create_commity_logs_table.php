<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommityLogsTable extends Migration
{

    public function up()
    {
        Schema::create('commity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('studant')->nullable();
            $table->string('CommityHead')->nullable();
            $table->boolean('isMedicalFit');
            $table->boolean('isCommityFit');
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('commity_logs');
    }
}
