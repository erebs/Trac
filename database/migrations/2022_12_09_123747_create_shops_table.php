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
            $table->string('shop_name',32)->default('');
            $table->string('mobile_number',13)->default('');
            $table->string('gst',15)->default('');
            $table->string('pincode',6)->default('');
            $table->string('constituency',)->default('');
            $table->string('shop_address',)->default('');
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
