<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("arabicFullName")->unique();
            $table->string("englishFullName")->nullable();
            $table->string("gender");
            $table->string("stdYear")->nullable();
            $table->string("pvType");
            $table->string("pvNumber");
            $table->string("religion");
            $table->string("birthCountry");
            $table->date("birthday");
            $table->string("address");
            $table->string("nationality");
            $table->string("phoneNumber1");
            $table->string("phoneNumber2")->nullable();
            $table->string("residencynumber")->nullable();
            $table->date("residencyExpire")->nullable();
            $table->string("parentName");
            $table->string("parentPhoneNumber1");
            $table->string("parentPhoneNumber2")->nullable();
            $table->string("relation");
            $table->date("applyDate");
            $table->string("CertType");
            $table->string("CertPercentage");
            $table->string("program");
            $table->string("collegeNumber")->nullable();
            $table->string("studentID")->nullable();
            $table->string("cardId")->nullable();
            $table->string("status");
            $table->string("academicStand");
            $table->integer('year');
            $table->string('class');
            $table->string('semester');
            $table->longText('pic');
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
        Schema::dropIfExists('studants');
    }
}
