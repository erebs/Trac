<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id')->nullable();
            $table->string('shop_name',32)->nullable();
            $table->string('mobile_number',13)->nullable();
            $table->string('gst',15)->nullable();
            $table->string('pincode',6)->nullable();
            $table->string('constituency',)->nullable();
            $table->string('shop_address',)->nullable();
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
        Schema::dropIfExists('shops');
    }
}
