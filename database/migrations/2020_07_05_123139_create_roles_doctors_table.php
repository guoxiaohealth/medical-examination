<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles_doctors', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('kind')->default(0);
            $table->boolean('role_is_admin')->default(false);
            $table->string('role_name')->default('');
            $table->string('doctor_name')->default('');
            $table->string('doctor_desc')->default('');
            $table->string('doctor_image')->default('');
            $table->boolean('doctor_can_meet')->default(true);
            $table->bigInteger('doctor_department_id')->default(0);
            $table->timestamps();
        });

        Schema::create('role_doctor_permission', function (Blueprint $table) {
            $table->bigInteger('role_doctor_id')->default(0);
            $table->bigInteger('permission_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_doctors');
        Schema::dropIfExists('role_doctor_permission');
    }
}
