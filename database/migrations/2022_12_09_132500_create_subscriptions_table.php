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
            $table->string('name', 32)->default('');
            $table->string('user_id');
            $table->string('shop_id');
            $table->string('transaction_id');
            $table->string('plans',64);
            $table->string('price')->default(0);
            $table->date('subscription_StartDate')->nullable();
            $table->date('subscription_EndDate')->nullable();
            
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
