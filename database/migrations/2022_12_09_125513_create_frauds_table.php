<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFraudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frauds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->string('name', 32);
            $table->string('mobile_number', 13);
            $table->string('address', 512);
            $table->string('profile_photo', 256);
            $table->enum('proof_type', ['Aadhaar', 'Pan Card', 'Voter ID', 'Driving Licence', 'Passport']);
            $table->string('proof_number', 32);
            $table->text('description');
            $table->string('approved_by', 256)->nullable();
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
        Schema::dropIfExists('frauds');
    }
}
