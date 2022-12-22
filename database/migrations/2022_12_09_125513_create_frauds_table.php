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
            $table->string('shop_id')->nullable();
            $table->string('name',32)->default('');
            $table->string('mobile_number',13)->default('');
            $table->string('address',512)->default('');
            $table->string('profile_photo',256)->nullable();
            $table->enum('type',['Aadhaar','Pancard','Voters-id','Licence','Passport'])->default('Aadhaar');
            $table->string('proof_number',32)->default('');
            $table->text('description',256)->default('');
            $table->string('approved_by',256)->nullable();
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
