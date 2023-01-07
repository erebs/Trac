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
            $table->integer('user_id');
            $table->string('shop_name',32)->required();
            $table->string('mobile_number',13)->required();
            $table->string('gst',15)->required();
            $table->string('pincode',6)->required();
            $table->string('constituency',)->required();
            $table->string('shop_address',)->required();
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
