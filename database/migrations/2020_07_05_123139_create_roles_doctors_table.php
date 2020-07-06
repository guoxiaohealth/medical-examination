<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('kind');
            $table->boolean('role_is_admin');
            $table->string('role_name');
            $table->string('doctor_desc');
            $table->string('doctor_image');
            $table->bigInteger('doctor_department_id');
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->string('role_id');
            $table->string('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_permission');
    }
}
