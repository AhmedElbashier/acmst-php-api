<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemLogsTable extends Migration
{

    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('operation');
            $table->integer('operation_id')->unsigned()->nullable();;
            $table->integer('person_id')->unsigned();
            $table->date('atdate');
            $table->string('notes');
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('system_logs');
    }
}
