<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',32)->default('');
            $table->string('transaction_id')->nullable();
            $table->string('shop_id')->nullable();
            $table->date('subscription_StartDate')->nullable();
            $table->date('subscription_EndDate')->nullable();
            $table->enum('subscription_status', ['Yes', 'No',''])->default('');
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
        Schema::dropIfExists('subscriptions');
    }

}
