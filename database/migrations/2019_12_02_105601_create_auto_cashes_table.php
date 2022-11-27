<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoCashesTable extends Migration
{

    public function up()
    {
        Schema::create('auto_cashes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transId')->nullable();
            // Constraints declaration

        });
    }

    public function down()
    {
        Schema::drop('auto_cashes');
    }
}
