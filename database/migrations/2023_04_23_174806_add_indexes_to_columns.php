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
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->index('telegram_id');
            $table->index('customer_id');
        });

        Schema::table('telegram_user_cards', function (Blueprint $table) {
            $table->index('telegram_user_id');
        });

        Schema::table('telegram_orders', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('telegram_order_items', function (Blueprint $table) {
            $table->index('order_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('party_id');
        });

        Schema::table('sales_parties', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('duties', function (Blueprint $table) {
            $table->index('customer_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone');
            $table->index('telephone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropIndex(['telegram_id']);
            $table->dropIndex(['customer_id']);
        });

        Schema::table('telegram_user_cards', function (Blueprint $table) {
            $table->dropIndex(['telegram_user_id']);
        });

        Schema::table('telegram_orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('telegram_order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['party_id']);
        });

        Schema::table('sales_parties', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('duties', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['telephone']);
        });
    }
};
