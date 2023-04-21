<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->bigInteger('price');
            $table->integer('place_id');
            $table->string('address');
            $table->string('state')->default('send_order');
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
        Schema::dropIfExists('telegram_orders');
    }
};
