<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('');
            $table->tinyInteger('sex')->default(0);
            $table->timestamp('birthday')->nullable(true);
            $table->string('mobile')->default('');
            $table->string('remarks')->default('');
            $table->bigInteger('member_kind_id')->default(0);
            $table->bigInteger('channel_id')->default(0);
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
        Schema::dropIfExists('members');
    }
}
