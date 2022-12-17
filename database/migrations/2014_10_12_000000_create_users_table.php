<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',32)->required();
            $table->integer('member_id')->nullable();
            $table->string('mobile_number','13')->nullable();
            $table->string('pincode','6')->nullable();
            $table->string('password','512')->nullable();
            $table->string('constituency','64')->nullable();
            $table->enum('status', ['Active', 'Pending', 'Suspended', 'Deleted'])->default('Pending');
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
        Schema::dropIfExists('users');
    }
}