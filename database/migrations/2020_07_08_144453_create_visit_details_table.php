<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visit_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('visit_id')->default(0);
            $table->bigInteger('manager_id')->default(0);
            $table->bigInteger('member_id')->default(0);
            $table->string('state')->default('');
            $table->string('remarks')->default('');
            $table->timestamp('plan_date')->nullable(true);
            $table->timestamp('real_date')->nullable(true);
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
        Schema::dropIfExists('visit_details');
    }
}
