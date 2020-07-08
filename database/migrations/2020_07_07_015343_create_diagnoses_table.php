<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id')->default(0);
            $table->bigInteger('subscribe_id')->default(0);
            $table->bigInteger('doctor_id')->default(0);
            $table->integer('times')->default(0);
            $table->string('no')->default('');
            $table->string('conclusion')->default('');
            $table->string('suggest')->default('');
            $table->string('remarks')->default('');
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
        Schema::dropIfExists('diagnoses');
    }
}
