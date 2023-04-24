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
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('telegram_user_id')->nullable();
        });

        Schema::table('sales_parties', function (Blueprint $table) {
            $table->integer('telegram_user_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('telegram_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        Schema::table('sales_parties', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });
    }
};
