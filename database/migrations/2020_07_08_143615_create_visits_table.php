<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(0);
            $table->tinyInteger('cycle')->default(0);
            $table->tinyInteger('day')->default(0);
            $table->timestamp('first_visit')->nullable();
            $table->bigInteger('manager_id')->default(0);
            $table->bigInteger('member_id')->default(0);
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
        Schema::dropIfExists('visits');
    }
}
