<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id')->default(0);
            $table->bigInteger('doctor_id')->default(0);
            $table->integer('times')->default(0);
            $table->json('kinds')->nullable(true);
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
        Schema::dropIfExists('medical_plans');
    }
}
