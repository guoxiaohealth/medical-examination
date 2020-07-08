<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalPlanOperatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_plan_operates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_doctor_id')->default(0);
            $table->bigInteger('medical_plan_id')->default(0);
            $table->tinyInteger('operate')->default(0);
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
        Schema::dropIfExists('medical_plan_operates');
    }
}
