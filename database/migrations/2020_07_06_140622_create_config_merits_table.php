<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigMeritsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_merits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mechanism_id')->default(0);
            $table->bigInteger('config_subject_id')->default(0);
            $table->string('name')->default('');
            $table->string('unit')->default('');
            $table->string('range')->default('');
            $table->tinyInteger('type')->default(0);
            $table->json('expression')->nullable(true);
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
        Schema::dropIfExists('config_merits');
    }
}
