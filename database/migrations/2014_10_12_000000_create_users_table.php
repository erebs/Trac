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
            $table->string('name',32);
            $table->string('member_id');
            $table->string('mobile_number','13');
            $table->string('pincode','6');
            // $table->string('district','32');
            $table->string('password','100');
            $table->string('constituency','64');
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
